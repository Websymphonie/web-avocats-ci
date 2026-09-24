<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class LearningAccessTest extends WebTestCase
{
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public function testAdminCanAccessTrainingBackoffice(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin/learning/trainings', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Formations');
    }

    public function testAdminCanOpenTrainingCreationForm(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin/learning/trainings/new', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Nouvelle formation');
        self::assertSelectorExists('form input[name$="[title]"]');
    }

    public function testBackofficeCreatesMuxLessonAndRestoresProviderAndPlaybackId(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        [$training, $module] = $this->createCourseModule();
        $newUrl = sprintf('/admin/learning/trainings/%d/modules/%d/lessons/new', $training->getId(), $module->getId());
        $client->request('GET', $newUrl, server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $form = $client->getCrawler()->selectButton('Enregistrer')->form();
        $formNames = $this->lessonFormFieldNames($client);
        $form[$formNames['title']] = 'Leçon Mux de test';
        $form[$formNames['provider']] = 'MUX';
        $form[$formNames['reference']] = 'TestPlaybackId0123456789';
        $client->submit($form);

        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());
        $row = $this->lessonRowByTitle('Leçon Mux de test');
        self::assertSame('MUX', $row['video_provider']);
        self::assertSame('TestPlaybackId0123456789', $row['external_video_id']);
        self::assertNull($row['video_url']);

        $lesson = $this->entityManager()->getRepository(LessonEntity::class)->findOneBy(['title' => 'Leçon Mux de test']);
        self::assertInstanceOf(LessonEntity::class, $lesson);
        $editUrl = sprintf('/admin/learning/trainings/%d/modules/%d/lessons/%d/edit', $training->getId(), $module->getId(), $lesson->getId());
        $client->request('GET', $editUrl, server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSame('MUX', $client->getCrawler()->filter('select[name$="[videoProvider]"] option[selected]')->attr('value'));
        self::assertSame('TestPlaybackId0123456789', $client->getCrawler()->filter('input[name$="[videoReference]"]')->attr('value'));
    }

    public function testBackofficeCanSwitchExistingLessonBetweenYouTubeAndMuxAndClearVideo(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        [$training, $module] = $this->createCourseModule();
        $lesson = (new LessonEntity())
            ->setModuleId($module->getId() ?? 0)
            ->setTitle('Leçon vidéo à modifier')
            ->setContent('<p>Contenu de test</p>')
            ->setVideoProvider('YOUTUBE')
            ->setVideoUrl('https://www.youtube.com/watch?v=M7lc1UVf-VE')
            ->setExternalVideoId('M7lc1UVf-VE')
            ->setPosition(1);
        $this->entityManager()->persist($lesson);
        $this->entityManager()->flush();

        $editUrl = sprintf('/admin/learning/trainings/%d/modules/%d/lessons/%d/edit', $training->getId(), $module->getId(), $lesson->getId());
        $client->request('GET', $editUrl, server: ['HTTPS' => 'on']);
        $form = $client->getCrawler()->selectButton('Enregistrer')->form();
        $formNames = $this->lessonFormFieldNames($client);
        $form[$formNames['provider']] = 'MUX';
        $form[$formNames['reference']] = 'TestPlaybackId0123456789';
        $client->submit($form);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());

        $row = $this->lessonRowById($lesson->getId() ?? 0);
        self::assertSame('MUX', $row['video_provider']);
        self::assertSame('TestPlaybackId0123456789', $row['external_video_id']);

        $client->request('GET', $editUrl, server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame('MUX', $client->getCrawler()->filter('select[name$="[videoProvider]"] option[selected]')->attr('value'));
        self::assertSame('TestPlaybackId0123456789', $client->getCrawler()->filter('input[name$="[videoReference]"]')->attr('value'));

        $form = $client->getCrawler()->selectButton('Enregistrer')->form();
        $formNames = $this->lessonFormFieldNames($client);
        $form[$formNames['provider']] = 'YOUTUBE';
        $form[$formNames['reference']] = 'https://youtu.be/M7lc1UVf-VE';
        $client->submit($form);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());

        $row = $this->lessonRowById($lesson->getId() ?? 0);
        self::assertSame('YOUTUBE', $row['video_provider']);
        self::assertSame('M7lc1UVf-VE', $row['external_video_id']);

        $client->request('GET', $editUrl, server: ['HTTPS' => 'on']);
        $form = $client->getCrawler()->selectButton('Enregistrer')->form();
        $formNames = $this->lessonFormFieldNames($client);
        $form[$formNames['provider']] = 'MUX';
        $form[$formNames['reference']] = '';
        $client->submit($form);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());

        $row = $this->lessonRowById($lesson->getId() ?? 0);
        self::assertNull($row['video_provider']);
        self::assertNull($row['external_video_id']);
    }

    public function testLiveFormUsesStableDeliveryModeValues(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin/learning/trainings/new/live', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['ONLINE', 'IN_PERSON', 'HYBRID'],
            $client->getCrawler()->filter('#training_form_deliveryMode option')->extract(['value']),
        );
    }

    public function testLiveFormPersistsIndependentMuxAndYouTubeSources(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin/learning/trainings/new/live', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();

        $crawler = $client->getCrawler();
        self::assertSame(['YOUTUBE', 'MUX'], $crawler->filter('select[name$="[liveVideoProvider]"] option')->extract(['value']));
        self::assertSame(['YOUTUBE', 'MUX'], $crawler->filter('select[name$="[replayVideoProvider]"] option')->extract(['value']));
        $form = $crawler->selectButton('Enregistrer le brouillon')->form();
        $startsAt = new DateTimeImmutable('+1 day');
        $form['training_form[title]'] = 'Live provider mixte';
        $form['training_form[summary]'] = 'Résumé de test';
        $form['training_form[description]'] = '<p>Description de test</p>';
        $form['training_form[startsAt]'] = $startsAt->format('Y-m-d\\TH:i');
        $form['training_form[endsAt]'] = $startsAt->modify('+1 hour')->format('Y-m-d\\TH:i');
        $form['training_form[deliveryMode]'] = 'ONLINE';
        $form['training_form[liveVideoProvider]'] = 'MUX';
        $form['training_form[liveVideoReference]'] = 'MuxLivePlaybackId012345';
        $form['training_form[replayVideoProvider]'] = 'YOUTUBE';
        $form['training_form[replayVideoReference]'] = 'https://youtu.be/M7lc1UVf-VE';
        $client->submit($form);

        self::assertResponseRedirects('/admin/learning/trainings');
        $training = $this->entityManager()->getRepository(TrainingEntity::class)->findOneBy(['title' => 'Live provider mixte']);
        self::assertInstanceOf(TrainingEntity::class, $training);
        $details = $this->entityManager()->getRepository(LiveTrainingDetailsEntity::class)->findOneBy(['trainingId' => $training->getId()]);
        self::assertInstanceOf(LiveTrainingDetailsEntity::class, $details);
        self::assertSame(VideoProvider::MUX, $details->getStreamProvider());
        self::assertSame('MuxLivePlaybackId012345', $details->getExternalStreamId());
        self::assertSame(VideoProvider::YOUTUBE, $details->getReplayProvider());
        self::assertSame('M7lc1UVf-VE', $details->getReplayExternalId());

        $client->request('GET', '/admin/learning/trainings/' . $training->getId() . '/edit', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame('MUX', $client->getCrawler()->filter('select[name$="[liveVideoProvider]"] option[selected]')->attr('value'));
        self::assertSame('MuxLivePlaybackId012345', $client->getCrawler()->filter('input[name$="[liveVideoReference]"]')->attr('value'));
        self::assertSame('YOUTUBE', $client->getCrawler()->filter('select[name$="[replayVideoProvider]"] option[selected]')->attr('value'));
        self::assertSame('https://www.youtube.com/watch?v=M7lc1UVf-VE', $client->getCrawler()->filter('input[name$="[replayVideoReference]"]')->attr('value'));
    }

    public function testAdminCourseStructureRouteIsProtectedByTheTrainingLookup(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin/learning/trainings/999999/structure', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/');
    }

    /** @dataProvider deniedRoles */
    public function testNonAdministratorsCannotAccessCourseStructure(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $client->request('GET', '/admin/learning/trainings/999999/structure', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /** @dataProvider deniedRoles */
    public function testNonAdministratorsAreDeniedFromTrainingBackoffice(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $client->request('GET', '/admin/learning/trainings', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonymousVisitorIsRedirectedToLogin(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/admin/learning/trainings', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    /** @return iterable<string, array{string}> */
    public static function deniedRoles(): iterable
    {
        yield 'avocat' => ['ROLE_AVOCAT'];
        yield 'user' => ['ROLE_USER'];
    }

    /** @param list<string> $roles */
    private function authenticatedClient(array $roles): KernelBrowser
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())
            ->setEmail(sprintf('learning-access-%d@example.test', ++self::$userSequence))
            ->setName('Learning Access Test')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $client->disableReboot();

        return $client;
    }

    private function clientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '15', 'number'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    /** @return array{TrainingEntity, CourseModuleEntity} */
    private function createCourseModule(): array
    {
        $training = (new TrainingEntity())
            ->setTitle('Formation persistance Mux')
            ->setSlug('formation-persistance-mux-' . bin2hex(random_bytes(5)))
            ->setSummary('Résumé')
            ->setDescription('<p>Description</p>');
        $this->entityManager()->persist($training);
        $this->entityManager()->flush();

        $module = (new CourseModuleEntity())
            ->setTrainingId($training->getId() ?? 0)
            ->setTitle('Module de test')
            ->setPosition(1);
        $this->entityManager()->persist($module);
        $this->entityManager()->flush();

        return [$training, $module];
    }

    /** @return array{title: string, provider: string, reference: string} */
    private function lessonFormFieldNames(KernelBrowser $client): array
    {
        $crawler = $client->getCrawler();

        return [
            'title' => (string) $crawler->filter('input[name$="[title]"]')->attr('name'),
            'provider' => (string) $crawler->filter('select[name$="[videoProvider]"]')->attr('name'),
            'reference' => (string) $crawler->filter('input[name$="[videoReference]"]')->attr('name'),
        ];
    }

    /** @return array{video_provider: string|null, external_video_id: string|null, video_url: string|null} */
    private function lessonRowByTitle(string $title): array
    {
        $row = $this->entityManager()->getConnection()->fetchAssociative(
            'SELECT video_provider, external_video_id, video_url FROM lesson WHERE title = :title',
            ['title' => $title],
        );
        self::assertIsArray($row);

        return $row;
    }

    /** @return array{video_provider: string|null, external_video_id: string|null, video_url: string|null} */
    private function lessonRowById(int $id): array
    {
        $row = $this->entityManager()->getConnection()->fetchAssociative(
            'SELECT video_provider, external_video_id, video_url FROM lesson WHERE id = :id',
            ['id' => $id],
        );
        self::assertIsArray($row);

        return $row;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
