<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
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
}
