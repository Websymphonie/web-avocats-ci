<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonResource\LessonResourceEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress\LessonProgressEntity;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class LearningMemberSecurityTest extends WebTestCase
{
    private static string $storageDirectory;
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-learning-security-' . bin2hex(random_bytes(5));

        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
            'APP_STORAGE_DIR' => self::$storageDirectory,
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem())->remove(self::$storageDirectory);
        parent::tearDownAfterClass();
    }

    public function testAnonymousCannotSelfEnroll(): void
    {
        $client = $this->clientWithSchema();
        $training = $this->createTraining(TrainingAccessType::FREE);

        $client->request('POST', '/espace/learning/trainings/' . $training->getUuidAsString() . '/enroll', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testFreeSelfEnrollmentIsSuccessfulAndIdempotent(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $client->loginUser($user);
        $client->disableReboot();
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        $url = '/espace/learning/trainings/' . $training->getUuidAsString() . '/enroll';
        $token = $this->csrfToken($client, 'learning_member_training_enroll_' . $training->getUuidAsString());
        $client->request('POST', $url, ['_token' => $token], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');
        $client->request('POST', $url, ['_token' => $token], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');

        $count = static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]);
        self::assertSame(1, $count);
    }

    public function testRoleUserCannotSelfEnrollInFreeTraining(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $client->loginUser($user);
        $client->disableReboot();
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        $client->request('POST', '/espace/learning/trainings/' . $training->getUuidAsString() . '/enroll', [
            '_token' => $this->csrfToken($client, 'learning_member_training_enroll_' . $training->getUuidAsString()),
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/espace');
        self::assertSame(0, static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count([
            'trainingId' => $training->getId(),
            'userId' => $user->getId(),
        ]));
    }

    /** @dataProvider nonSelfEnrollAccessTypes */
    public function testPaidAndRestrictedSelfEnrollmentAreDenied(TrainingAccessType $accessType): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining($accessType);
        $client->loginUser($user);
        $client->disableReboot();
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);
        $client->request('POST', '/espace/learning/trainings/' . $training->getUuidAsString() . '/enroll', ['_token' => $this->csrfToken($client, 'learning_member_training_enroll_' . $training->getUuidAsString())], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/espace');
        self::assertSame(0, static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
    }

    public function testBulkDeletePrevalidatesEnrollmentsAndDoesNotPartiallyDelete(): void
    {
        $client = $this->clientWithSchema();
        $admin = $this->createUser(['ROLE_ADMIN']);
        $deletable = $this->createTraining(TrainingAccessType::FREE);
        $blocked = $this->createTraining(TrainingAccessType::FREE);
        $this->createEnrollment($blocked, $admin, EnrollmentStatus::ACTIVE);
        $client->loginUser($admin);
        $client->disableReboot();

        $client->request('POST', '/admin/learning/trainings/bulk-delete', [
            '_token' => $this->csrfToken($client, 'training-bulk-delete'),
            'ids' => [$deletable->getId(), $blocked->getId()],
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/learning/trainings');
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(TrainingEntity::class);
        self::assertNotNull($repository->find($deletable->getId()));
        self::assertNotNull($repository->find($blocked->getId()));
        self::assertStringContainsString('inscriptions', (string) $client->getSession()->getFlashBag()->all()['danger'][0] ?? '');
    }

    public function testBulkDeleteRemovesAllDeletableTrainings(): void
    {
        $client = $this->clientWithSchema();
        $admin = $this->createUser(['ROLE_ADMIN']);
        $first = $this->createTraining(TrainingAccessType::FREE);
        $second = $this->createTraining(TrainingAccessType::FREE);
        $firstId = $first->getId();
        $secondId = $second->getId();
        $client->loginUser($admin);
        $client->disableReboot();

        $client->request('POST', '/admin/learning/trainings/bulk-delete', [
            '_token' => $this->csrfToken($client, 'training-bulk-delete'),
            'ids' => [$firstId, $secondId],
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/learning/trainings');
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(TrainingEntity::class);
        self::assertNull($repository->find($firstId));
        self::assertNull($repository->find($secondId));
    }

    /** @return iterable<string, array{TrainingAccessType}> */
    public static function nonSelfEnrollAccessTypes(): iterable
    {
        yield 'paid' => [TrainingAccessType::PAID];
        yield 'restricted' => [TrainingAccessType::RESTRICTED];
    }

    public function testActiveEnrollmentCanDownloadAResourceAndMissingPhysicalFileReturns404(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        [$resource, $fileName] = $this->createResource($training, true);
        $this->createEnrollment($training, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));

        (new Filesystem())->remove(self::$storageDirectory . '/private/learning/resources/' . $fileName);
        $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testRoleUserWithActiveEnrollmentCannotUseLearningSurfaces(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        [$resource] = $this->createResource($training, true);
        $lesson = $this->entityManager()->getRepository(LessonEntity::class)->find($resource->getLessonId());
        self::assertInstanceOf(LessonEntity::class, $lesson);
        $this->createEnrollment($training, $user, EnrollmentStatus::ACTIVE);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $this->createEnrollment($live, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/');

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);
        $client->request('POST', '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/start', [
            '_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $lesson->getUuidAsString()),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');
        self::assertSame(0, static::getContainer()->get('doctrine')->getManager()->getRepository(LessonProgressEntity::class)->count([]));

        $client->request('GET', '/espace/learning/trainings/' . $live->getUuidAsString() . '/join', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertNotSame('https://meet.example.test/live', $client->getResponse()->headers->get('Location'));
    }

    public function testAdminGrantRequiresAnEligibleAvocat(): void
    {
        $client = $this->clientWithSchema();
        $admin = $this->createUser(['ROLE_ADMIN']);
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $client->loginUser($admin);
        $client->disableReboot();

        $grantUrl = '/admin/learning/trainings/' . $training->getId() . '/enrollments/grant';
        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $avocat->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');
        self::assertSame(1, static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count([
            'trainingId' => $training->getId(),
            'userId' => $avocat->getId(),
        ]));

        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $user->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');
        self::assertSame(0, static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count([
            'trainingId' => $training->getId(),
            'userId' => $user->getId(),
        ]));
    }

    public function testResourceDownloadIsDeniedWithoutEnrollmentAndAgainstAnotherTraining(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $trainingA = $this->createTraining(TrainingAccessType::FREE);
        $trainingB = $this->createTraining(TrainingAccessType::FREE);
        [$resource] = $this->createResource($trainingB, true);
        $this->createEnrollment($trainingA, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/');
    }

    public function testRevokedDraftAndArchivedTrainingCannotExposeResources(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $client->loginUser($user);
        $client->disableReboot();

        foreach ([TrainingStatus::DRAFT, TrainingStatus::ARCHIVED] as $status) {
            $training = $this->createTraining(TrainingAccessType::FREE, $status);
            [$resource] = $this->createResource($training, true);
            $this->createEnrollment($training, $user, EnrollmentStatus::ACTIVE);
            $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FOUND, $status->value);
        }

        $training = $this->createTraining(TrainingAccessType::FREE);
        [$resource] = $this->createResource($training, true);
        $this->createEnrollment($training, $user, EnrollmentStatus::REVOKED);
        $client->request('GET', '/espace/learning/resources/' . $resource->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testOnlineLiveJoinRedirectsOnlyForActiveEnrollment(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $this->createEnrollment($live, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/trainings/' . $live->getUuidAsString() . '/join', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('https://meet.example.test/live');
    }

    public function testLiveJoinIdorAndInactiveTrainingAreDenied(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $liveA = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, 'https://meet.example.test/a');
        $liveB = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, 'https://meet.example.test/b');
        $this->createEnrollment($liveA, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/trainings/' . $liveB->getUuidAsString() . '/join', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/');

        foreach ([TrainingStatus::DRAFT, TrainingStatus::ARCHIVED] as $status) {
            $inactive = $this->createLive(LiveDeliveryMode::ONLINE, $status);
            $this->createEnrollment($inactive, $user, EnrollmentStatus::ACTIVE);
            $client->request('GET', '/espace/learning/trainings/' . $inactive->getUuidAsString() . '/join', server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FOUND, $status->value);
        }
    }

    public function testInPersonJoinReturnsConflictWithoutRedirectLocation(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::IN_PERSON, TrainingStatus::PUBLISHED, null, 'Maison de l’Avocat');
        $this->createEnrollment($live, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $client->request('GET', '/espace/learning/trainings/' . $live->getUuidAsString() . '/join', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertNull($client->getResponse()->headers->get('Location'));
    }

    public function testCourseLessonProgressStartCompleteAndReactivateAreIdempotent(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $lesson = $this->createCourseLesson($training, 1);
        $this->createEnrollment($training, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);
        $client->disableReboot();

        $startUrl = '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/start';
        $completeUrl = '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/complete';
        $client->request('POST', $startUrl, ['_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $lesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');
        $client->request('POST', $completeUrl, ['_token' => $this->csrfToken($client, 'learning_member_lesson_complete_' . $lesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');
        $client->request('POST', $completeUrl, ['_token' => $this->csrfToken($client, 'learning_member_lesson_complete_' . $lesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');

        $progress = $this->entityManager()->getRepository(LessonProgressEntity::class)->findOneBy(['enrollmentId' => 1, 'lessonId' => $lesson->getId()]);
        self::assertInstanceOf(LessonProgressEntity::class, $progress);
        self::assertSame('COMPLETED', $progress->getStatus()->value);
        self::assertNotNull($progress->getCompletedAt());
        self::assertNotSame($progress->getCompletedAt(), $progress->getStartedAt());
        $summary = static::getContainer()->get(LessonProgressRepositoryInterface::class)->summarizeByEnrollmentIds([$progress->getEnrollmentId()], 1)[$progress->getEnrollmentId()];
        self::assertSame(1, $summary->startedLessons);
        self::assertSame(1, $summary->completedLessons);
        self::assertSame(100, $summary->progressPercentage);
        self::assertNotNull($summary->lastActivityAt);

        $admin = $this->createUser(['ROLE_ADMIN']);
        $client->loginUser($admin);
        $client->request('DELETE', '/admin/learning/trainings/' . $training->getId() . '/modules/' . $lesson->getModuleId() . '/lessons/' . $lesson->getId() . '/delete', ['_token' => $this->csrfToken($client, 'learning_lesson_delete_' . $training->getId() . '_' . $lesson->getId())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());
        self::assertNotNull($this->entityManager()->getRepository(LessonEntity::class)->find($lesson->getId()));
        $client->request('DELETE', '/admin/learning/trainings/' . $training->getId() . '/modules/' . $lesson->getModuleId() . '/delete', ['_token' => $this->csrfToken($client, 'learning_course_module_delete_' . $training->getId() . '_' . $lesson->getModuleId())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId());
        self::assertNotNull($this->entityManager()->getRepository(CourseModuleEntity::class)->find($lesson->getModuleId()));
    }

    public function testLessonProgressRequiresPublishedCourseAndActiveEnrollment(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $client->loginUser($user);
        $client->disableReboot();

        $draft = $this->createTraining(TrainingAccessType::FREE, TrainingStatus::DRAFT);
        $draftLesson = $this->createCourseLesson($draft, 1);
        $this->createEnrollment($draft, $user, EnrollmentStatus::ACTIVE);
        $client->request('POST', '/espace/learning/lessons/' . $draftLesson->getUuidAsString() . '/start', ['_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $draftLesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');

        $published = $this->createTraining(TrainingAccessType::FREE);
        $publishedLesson = $this->createCourseLesson($published, 1);
        $this->createEnrollment($published, $user, EnrollmentStatus::REVOKED);
        $client->request('POST', '/espace/learning/lessons/' . $publishedLesson->getUuidAsString() . '/start', ['_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $publishedLesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');

        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $liveLesson = $this->createCourseLesson($live, 1);
        $this->createEnrollment($live, $user, EnrollmentStatus::ACTIVE);
        $client->request('POST', '/espace/learning/lessons/' . $liveLesson->getUuidAsString() . '/start', ['_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $liveLesson->getUuidAsString())], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/espace');
        self::assertSame(0, $this->entityManager()->getRepository(LessonProgressEntity::class)->count([]));
    }

    public function testRepresentativeInvalidCsrfTokensDoNotExecuteMutations(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        foreach ([
            ['POST', '/admin/learning/trainings/1/publish'],
            ['DELETE', '/admin/learning/categories/1/delete'],
            ['DELETE', '/admin/learning/tags/1/delete'],
            ['POST', '/admin/learning/trainings/1/enrollments/grant'],
            ['POST', '/admin/learning/trainings/1/modules/reorder'],
            ['POST', '/admin/learning/trainings/1/modules/1/lessons/1/resources/1/rename'],
        ] as [$method, $url]) {
            $client->request($method, $url, ['_token' => 'invalid'], server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $method . ' ' . $url);
        }
    }

    private function authenticatedClient(array $roles): KernelBrowser
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser($roles);
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
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        return $client;
    }

    private function createUser(array $roles): User
    {
        $user = (new User())
            ->setEmail(sprintf('learning-member-%d@example.test', ++self::$userSequence))
            ->setName('Learning Member')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);
        $this->entityManager()->persist($user);
        $this->entityManager()->flush();
        return $user;
    }

    private function createTraining(TrainingAccessType $accessType, TrainingStatus $status = TrainingStatus::PUBLISHED): TrainingEntity
    {
        $suffix = bin2hex(random_bytes(4));
        $training = (new TrainingEntity())
            ->setTitle('Formation ' . $suffix)
            ->setSlug('formation-' . $suffix)
            ->setSummary('Résumé')
            ->setDescription('<p>Description</p>')
            ->setAccessType($accessType)
            ->setStatus($status)
            ->setPublishedAt($status === TrainingStatus::PUBLISHED ? new DateTimeImmutable() : null);
        $this->entityManager()->persist($training);
        $this->entityManager()->flush();
        return $training;
    }

    private function createCourseLesson(TrainingEntity $training, int $position): LessonEntity
    {
        $module = (new CourseModuleEntity())->setTrainingId($training->getId() ?? 0)->setTitle('Module ' . $position)->setPosition($position);
        $this->entityManager()->persist($module);
        $this->entityManager()->flush();
        $lesson = (new LessonEntity())->setModuleId($module->getId() ?? 0)->setTitle('Leçon ' . $position)->setContent('<p>Contenu</p>')->setPosition(1);
        $this->entityManager()->persist($lesson);
        $this->entityManager()->flush();
        return $lesson;
    }

    private function createLive(LiveDeliveryMode $mode, TrainingStatus $status, ?string $joinUrl = 'https://meet.example.test/live', ?string $location = null): TrainingEntity
    {
        $training = (new TrainingEntity(TrainingType::LIVE))
            ->setTitle('Live ' . bin2hex(random_bytes(4)))
            ->setSlug('live-' . bin2hex(random_bytes(4)))
            ->setSummary('Résumé')
            ->setDescription('<p>Description</p>')
            ->setAccessType(TrainingAccessType::FREE)
            ->setStatus($status)
            ->setPublishedAt($status === TrainingStatus::PUBLISHED ? new DateTimeImmutable() : null);
        $this->entityManager()->persist($training);
        $this->entityManager()->flush();
        $details = (new LiveTrainingDetailsEntity())
            ->setTrainingId($training->getId() ?? 0)
            ->setStartsAt(new DateTimeImmutable('+1 day 10:00'))
            ->setEndsAt(new DateTimeImmutable('+1 day 11:00'))
            ->setDeliveryMode($mode)
            ->setLocation($location)
            ->setJoinUrl($joinUrl);
        $this->entityManager()->persist($details);
        $this->entityManager()->flush();
        return $training;
    }

    /** @return array{LessonResourceEntity, string} */
    private function createResource(TrainingEntity $training, bool $physicalFile): array
    {
        $module = (new CourseModuleEntity())->setTrainingId($training->getId() ?? 0)->setTitle('Module')->setPosition(1);
        $this->entityManager()->persist($module);
        $this->entityManager()->flush();
        $lesson = (new LessonEntity())->setModuleId($module->getId() ?? 0)->setTitle('Leçon')->setContent('<p>Contenu</p>')->setPosition(1);
        $this->entityManager()->persist($lesson);
        $this->entityManager()->flush();
        $fileName = bin2hex(random_bytes(24)) . '.pdf';
        $file = (new StoredFileEntity())->setOriginalName('support.pdf')->setStorageName('learning/resources/' . $fileName)->setMimeType('application/pdf')->setSize(4)->setChecksum(hash('sha256', 'test'));
        $this->entityManager()->persist($file);
        $this->entityManager()->flush();
        if ($physicalFile) {
            (new Filesystem())->dumpFile(self::$storageDirectory . '/private/learning/resources/' . $fileName, 'test');
        }
        $resource = (new LessonResourceEntity())->setLessonId($lesson->getId() ?? 0)->setStoredFileId($file->getId() ?? 0)->setTitle('Support')->setPosition(1);
        $this->entityManager()->persist($resource);
        $this->entityManager()->flush();
        return [$resource, $fileName];
    }

    private function createEnrollment(TrainingEntity $training, User $user, EnrollmentStatus $status): void
    {
        $enrollment = (new EnrollmentEntity())->setTrainingId($training->getId() ?? 0)->setUserId($user->getId() ?? 0)->setStatus($status)->setSource(EnrollmentSource::SELF_SERVICE)->setActivatedAt(new DateTimeImmutable());
        $this->entityManager()->persist($enrollment);
        $this->entityManager()->flush();
    }

    private function csrfToken(KernelBrowser $client, string $id): string
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']);
        $request->setSession($client->getSession());
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);
        try {
            $token = static::getContainer()->get('security.csrf.token_manager')->getToken($id)->getValue();
            $request->getSession()->save();
            return $token;
        } finally {
            $requestStack->pop();
        }
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
