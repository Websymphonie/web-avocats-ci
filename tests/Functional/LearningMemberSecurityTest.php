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
use Websymphonie\LearningContext\Domain\Enum\LiveStreamProvider;
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
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
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

    public function testAnonymousCannotOpenMemberTrainingList(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/espace/formations', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testAvocatSeesOnlyPublishedActiveEnrollmentsOnMemberTrainingList(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $otherUser = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $this->createCourseLesson($course, 1);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $revoked = $this->createTraining(TrainingAccessType::FREE);
        $draft = $this->createTraining(TrainingAccessType::FREE, TrainingStatus::DRAFT);
        $other = $this->createTraining(TrainingAccessType::FREE);
        $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($live, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($revoked, $avocat, EnrollmentStatus::REVOKED);
        $this->createEnrollment($draft, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($other, $otherUser, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($course->getTitle(), $content);
        self::assertStringContainsString($live->getTitle(), $content);
        self::assertStringNotContainsString($revoked->getTitle(), $content);
        self::assertStringNotContainsString($draft->getTitle(), $content);
        self::assertStringNotContainsString($other->getTitle(), $content);
    }

    public function testAvocatDashboardShowsUpcomingLivesInChronologicalOrderAndExcludesOtherUsers(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $otherUser = $this->createUser(['ROLE_AVOCAT']);
        $latest = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, startsAt: new DateTimeImmutable('+3 days 10:00'));
        $soonest = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, startsAt: new DateTimeImmutable('+1 day 10:00'));
        $otherLive = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, startsAt: new DateTimeImmutable('+2 days 10:00'));
        $this->createEnrollment($latest, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($soonest, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($otherLive, $otherUser, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($soonest->getTitle(), $content);
        self::assertStringContainsString($latest->getTitle(), $content);
        self::assertStringNotContainsString($otherLive->getTitle(), $content);
        self::assertLessThan(strpos($content, $latest->getTitle()), strpos($content, $soonest->getTitle()));
        self::assertStringContainsString('/espace/learning/trainings/' . $soonest->getUuidAsString() . '/join', $content);
        self::assertStringNotContainsString('https://meet.example.test/live', $content);
    }

    public function testAvocatDashboardPrioritizesInProgressCourseAndExcludesOtherUsers(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $otherUser = $this->createUser(['ROLE_AVOCAT']);

        $notStarted = $this->createTraining(TrainingAccessType::FREE);
        $inProgress = $this->createTraining(TrainingAccessType::FREE);
        $completed = $this->createTraining(TrainingAccessType::FREE);
        $otherCourse = $this->createTraining(TrainingAccessType::FREE);

        $notStartedLesson = $this->createCourseLesson($notStarted, 1);
        $inProgressLessons = [];
        for ($position = 1; $position <= 9; ++$position) {
            $inProgressLessons[] = $this->createCourseLesson($inProgress, $position);
        }
        $completedLesson = $this->createCourseLesson($completed, 1);
        $otherLesson = $this->createCourseLesson($otherCourse, 1);

        $this->createEnrollment($notStarted, $avocat, EnrollmentStatus::ACTIVE);
        $inProgressEnrollment = $this->createEnrollment($inProgress, $avocat, EnrollmentStatus::ACTIVE);
        $completedEnrollment = $this->createEnrollment($completed, $avocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($otherCourse, $otherUser, EnrollmentStatus::ACTIVE);

        foreach (array_slice($inProgressLessons, 0, 4) as $lesson) {
            $this->markLessonCompleted($inProgressEnrollment, $lesson);
        }
        $this->markLessonCompleted($completedEnrollment, $completedLesson);
        $this->entityManager()->flush();

        $client->loginUser($avocat);
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($inProgress->getTitle(), $content);
        self::assertStringContainsString('44 %', $content);
        self::assertStringContainsString('Continuer', $content);
        self::assertStringContainsString('/espace/formations/' . $inProgress->getUuidAsString(), $content);
        self::assertStringNotContainsString($notStarted->getTitle(), $content);
        self::assertStringNotContainsString($completed->getTitle(), $content);
        self::assertStringNotContainsString($otherCourse->getTitle(), $content);
        self::assertStringNotContainsString((string) $notStartedLesson->getTitle(), $content);
        self::assertStringNotContainsString((string) $otherLesson->getTitle(), $content);
    }

    public function testAvocatDashboardFallsBackToNotStartedCourse(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $this->createCourseLesson($course, 1);
        $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($course->getTitle(), $content);
        self::assertStringContainsString('0 %', $content);
        self::assertStringContainsString('Commencer', $content);
        self::assertStringContainsString('/espace/formations/' . $course->getUuidAsString(), $content);
    }

    public function testRoleUserDashboardDoesNotExposeUpcomingLives(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $this->createEnrollment($live, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Aucun rendez-vous à afficher', $content);
        self::assertStringNotContainsString($live->getTitle(), $content);
    }

    public function testAvocatDashboardKeepsEmptyStateWithoutUpcomingLive(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $pastLive = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, startsAt: new DateTimeImmutable('-2 days 10:00'));
        $this->createEnrollment($pastLive, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Aucun rendez-vous à afficher', $content);
        self::assertStringContainsString('Votre parcours apparaîtra ici', $content);
    }

    public function testRoleUserWithActiveEnrollmentSeesEmptyMemberTrainingState(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $this->createEnrollment($training, $user, EnrollmentStatus::ACTIVE);
        $client->loginUser($user);

        $client->request('GET', '/espace/formations', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Vous n’avez encore aucune formation accessible.', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString($training->getTitle(), (string) $client->getResponse()->getContent());
    }

    public function testMemberTrainingListDisplaysCourseProgress(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $lesson = $this->createCourseLesson($course, 1);
        $enrollment = $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $progress = (new LessonProgressEntity())
            ->setEnrollmentId($enrollment->getId() ?? 0)
            ->setLessonId($lesson->getId() ?? 0)
            ->setStatus(\Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus::COMPLETED)
            ->setStartedAt(new DateTimeImmutable('-1 hour'))
            ->setLastAccessedAt(new DateTimeImmutable('-10 minutes'))
            ->setCompletedAt(new DateTimeImmutable('-10 minutes'));
        $this->entityManager()->persist($progress);
        $this->entityManager()->flush();
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('100 %', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Formation terminée', (string) $client->getResponse()->getContent());
    }

    public function testAvocatCanOpenCoursePlayerAndResumeFirstIncompleteLesson(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $completedLesson = $this->createCourseLesson($course, 1);
        $activeLesson = $this->createCourseLesson($course, 2);
        $enrollment = $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $progress = (new LessonProgressEntity())
            ->setEnrollmentId($enrollment->getId() ?? 0)
            ->setLessonId($completedLesson->getId() ?? 0)
            ->setStatus(\Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus::COMPLETED)
            ->setStartedAt(new DateTimeImmutable('-1 hour'))
            ->setLastAccessedAt(new DateTimeImmutable('-10 minutes'))
            ->setCompletedAt(new DateTimeImmutable('-10 minutes'));
        $this->entityManager()->persist($progress);
        $this->entityManager()->flush();
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $course->getUuidAsString(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($course->getTitle(), $content);
        self::assertStringContainsString($activeLesson->getTitle(), $content);
        self::assertStringContainsString('50 %', $content);
        self::assertStringContainsString('/espace/formations/' . $course->getUuidAsString() . '/lecons/' . $activeLesson->getUuidAsString(), $content);
        self::assertStringNotContainsString('meet.example.test', $content);
        self::assertStringNotContainsString('youtube-nocookie.com', $content);
    }

    public function testAvocatCanRenderAYouTubeLessonInTheProtectedCoursePlayer(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $lesson = $this->createCourseLesson($course, 1)
            ->setVideoProvider('YOUTUBE')
            ->setVideoUrl('https://www.youtube.com/watch?v=M7lc1UVf-VE')
            ->setExternalVideoId('M7lc1UVf-VE');
        $this->entityManager()->flush();
        $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $course->getUuidAsString() . '/lecons/' . $lesson->getUuidAsString(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', $content);
        self::assertStringContainsString('title="Vidéo de la leçon ' . $lesson->getTitle() . '"', $content);
        self::assertStringContainsString('allowfullscreen', $content);
        self::assertStringNotContainsString('autoplay=1', $content);
    }

    public function testAvocatCanOpenOwnLiveDetailsWithoutExposingJoinUrl(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $this->createEnrollment($live, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $live->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($live->getTitle(), $content);
        self::assertStringContainsString('En ligne', $content);
        self::assertStringContainsString('À venir', $content);
        self::assertStringContainsString('/espace/learning/trainings/' . $live->getUuidAsString() . '/join', $content);
        self::assertStringNotContainsString('https://meet.example.test/live', $content);
    }

    public function testAvocatCanRenderYoutubeLiveWithoutExposingJoinUrl(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, 'https://meet.example.test/live');
        $details = $this->entityManager()->getRepository(LiveTrainingDetailsEntity::class)->findOneBy(['trainingId' => $live->getId()]);
        self::assertInstanceOf(LiveTrainingDetailsEntity::class, $details);
        $details->setStreamProvider(LiveStreamProvider::YOUTUBE)->setExternalStreamId('M7lc1UVf-VE');
        $this->entityManager()->flush();
        $this->createEnrollment($live, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $live->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', $content);
        self::assertStringContainsString('Diffusion en direct — ' . $live->getTitle(), $content);
        self::assertStringContainsString('allowfullscreen', $content);
        self::assertStringContainsString('/espace/learning/trainings/' . $live->getUuidAsString() . '/join', $content);
        self::assertStringNotContainsString('https://meet.example.test/live', $content);
    }

    public function testAvocatCanRenderYoutubeOnlyLiveWithoutJoinUrl(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, null);
        $details = $this->entityManager()->getRepository(LiveTrainingDetailsEntity::class)->findOneBy(['trainingId' => $live->getId()]);
        self::assertInstanceOf(LiveTrainingDetailsEntity::class, $details);
        $details->setStreamProvider(LiveStreamProvider::YOUTUBE)->setExternalStreamId('M7lc1UVf-VE');
        $this->entityManager()->flush();
        $this->createEnrollment($live, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $live->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('/espace/learning/trainings/' . $live->getUuidAsString() . '/join', (string) $client->getResponse()->getContent());
    }

    public function testHybridYoutubeLiveKeepsLocationAndSecureJoinFallback(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $live = $this->createLive(LiveDeliveryMode::HYBRID, TrainingStatus::PUBLISHED, 'https://meet.example.test/hybrid', 'Maison de l’Avocat');
        $details = $this->entityManager()->getRepository(LiveTrainingDetailsEntity::class)->findOneBy(['trainingId' => $live->getId()]);
        self::assertInstanceOf(LiveTrainingDetailsEntity::class, $details);
        $details->setStreamProvider(LiveStreamProvider::YOUTUBE)->setExternalStreamId('M7lc1UVf-VE');
        $this->entityManager()->flush();
        $this->createEnrollment($live, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $live->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Maison de l’Avocat', $content);
        self::assertStringContainsString('https://www.youtube-nocookie.com/embed/M7lc1UVf-VE', $content);
        self::assertStringContainsString('/espace/learning/trainings/' . $live->getUuidAsString() . '/join', $content);
        self::assertStringNotContainsString('https://meet.example.test/hybrid', $content);
    }

    public function testLiveDetailsShowHybridLocationAndDenyUnauthorizedMembers(): void
    {
        $client = $this->clientWithSchema();
        $owner = $this->createUser(['ROLE_AVOCAT']);
        $otherAvocat = $this->createUser(['ROLE_AVOCAT']);
        $roleUser = $this->createUser(['ROLE_USER']);
        $hybrid = $this->createLive(LiveDeliveryMode::HYBRID, TrainingStatus::PUBLISHED, 'https://meet.example.test/hybrid', 'Maison de l’Avocat');
        $unowned = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED, 'https://meet.example.test/unowned');
        $course = $this->createTraining(TrainingAccessType::FREE);
        $this->createEnrollment($hybrid, $owner, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($unowned, $otherAvocat, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($hybrid, $roleUser, EnrollmentStatus::ACTIVE);
        $this->createEnrollment($course, $owner, EnrollmentStatus::ACTIVE);
        $client->loginUser($owner);
        $client->disableReboot();

        $client->request('GET', '/espace/formations/' . $hybrid->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Hybride', $content);
        self::assertStringContainsString('Maison de l’Avocat', $content);
        self::assertStringNotContainsString('https://meet.example.test/hybrid', $content);

        $client->loginUser($owner);
        $client->request('GET', '/espace/formations/' . $unowned->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->loginUser($otherAvocat);
        $client->request('GET', '/espace/formations/' . $hybrid->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/espace/formations/' . $course->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->loginUser($roleUser);
        $client->request('GET', '/espace/formations/' . $hybrid->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

    }

    public function testAnonymousCannotOpenLiveDetails(): void
    {
        $client = $this->clientWithSchema();
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);

        $client->request('GET', '/espace/formations/' . $live->getUuidAsString() . '/live', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testCoursePlayerDisplaysResourcesThroughProtectedDownloadRoute(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        [$resource] = $this->createResource($course, false);
        $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);

        $client->request('GET', '/espace/formations/' . $course->getUuidAsString(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Support', $content);
        self::assertStringContainsString('/espace/learning/resources/' . $resource->getUuidAsString() . '/download', $content);
        self::assertStringNotContainsString('private/learning/resources/', $content);
    }

    public function testCoursePlayerDeniesOtherUserRoleUserAndLive(): void
    {
        $client = $this->clientWithSchema();
        $owner = $this->createUser(['ROLE_AVOCAT']);
        $otherAvocat = $this->createUser(['ROLE_AVOCAT']);
        $roleUser = $this->createUser(['ROLE_USER']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $this->createCourseLesson($course, 1);
        $this->createEnrollment($course, $owner, EnrollmentStatus::ACTIVE);
        $live = $this->createLive(LiveDeliveryMode::ONLINE, TrainingStatus::PUBLISHED);
        $this->createCourseLesson($live, 1);
        $this->createEnrollment($live, $owner, EnrollmentStatus::ACTIVE);
        $client->disableReboot();

        foreach ([$otherAvocat, $roleUser] as $user) {
            $client->loginUser($user);
            $client->request('GET', '/espace/formations/' . $course->getUuidAsString(), server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }

        $client->loginUser($owner);
        $client->request('GET', '/espace/formations/' . $live->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCoursePlayerProgressActionsAreCsrfProtectedAndReturnToPlayer(): void
    {
        $client = $this->clientWithSchema();
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $course = $this->createTraining(TrainingAccessType::FREE);
        $lesson = $this->createCourseLesson($course, 1);
        $enrollment = $this->createEnrollment($course, $avocat, EnrollmentStatus::ACTIVE);
        $client->loginUser($avocat);
        $client->disableReboot();
        $playerUrl = '/espace/formations/' . $course->getUuidAsString() . '/lecons/' . $lesson->getUuidAsString();

        $client->request('POST', '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/start', [
            '_token' => 'invalid',
            '_return_to' => $playerUrl,
        ], server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $client->request('POST', '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/start', [
            '_token' => $this->csrfToken($client, 'learning_member_lesson_start_' . $lesson->getUuidAsString()),
            '_return_to' => $playerUrl,
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects($playerUrl);

        $client->request('POST', '/espace/learning/lessons/' . $lesson->getUuidAsString() . '/complete', [
            '_token' => $this->csrfToken($client, 'learning_member_lesson_complete_' . $lesson->getUuidAsString()),
            '_return_to' => $playerUrl,
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects($playerUrl);

        $progress = $this->entityManager()->getRepository(LessonProgressEntity::class)->findOneBy([
            'enrollmentId' => $enrollment->getId(),
            'lessonId' => $lesson->getId(),
        ]);
        self::assertInstanceOf(LessonProgressEntity::class, $progress);
        self::assertSame('COMPLETED', $progress->getStatus()->value);
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
        self::assertSame(1, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
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
        self::assertSame(0, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
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
        self::assertSame(1, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $avocat]));

        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $user->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');
        self::assertSame(0, static::getContainer()->get('doctrine')->getManager()->getRepository(EnrollmentEntity::class)->count([
            'trainingId' => $training->getId(),
            'userId' => $user->getId(),
        ]));
        self::assertSame(0, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
    }

    public function testRevokeAndReactivateCreateDistinctNotifications(): void
    {
        $client = $this->clientWithSchema();
        $admin = $this->createUser(['ROLE_ADMIN']);
        $avocat = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::FREE);
        $client->loginUser($admin);
        $client->disableReboot();

        $grantUrl = '/admin/learning/trainings/' . $training->getId() . '/enrollments/grant';
        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $avocat->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');

        $enrollment = $this->entityManager()->getRepository(EnrollmentEntity::class)->findOneBy([
            'trainingId' => $training->getId(),
            'userId' => $avocat->getId(),
        ]);
        self::assertInstanceOf(EnrollmentEntity::class, $enrollment);

        $revokeUrl = '/admin/learning/trainings/' . $training->getId() . '/enrollments/' . $enrollment->getId() . '/revoke';
        $client->request('POST', $revokeUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_revoke_' . $enrollment->getId()),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');
        $client->request('POST', $revokeUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_revoke_' . $enrollment->getId()),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');

        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $avocat->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');
        $client->request('POST', $grantUrl, [
            '_token' => $this->csrfToken($client, 'learning_enrollment_grant_' . $training->getId()),
            'userId' => $avocat->getId(),
        ], server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/learning/trainings/' . $training->getId() . '/enrollments');

        self::assertSame(3, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $avocat]));
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

    private function createLive(LiveDeliveryMode $mode, TrainingStatus $status, ?string $joinUrl = 'https://meet.example.test/live', ?string $location = null, ?DateTimeImmutable $startsAt = null): TrainingEntity
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
            ->setStartsAt($startsAt ?? new DateTimeImmutable('+1 day 10:00'))
            ->setEndsAt(($startsAt ?? new DateTimeImmutable('+1 day 10:00'))->modify('+1 hour'))
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

    private function createEnrollment(TrainingEntity $training, User $user, EnrollmentStatus $status): EnrollmentEntity
    {
        $enrollment = (new EnrollmentEntity())->setTrainingId($training->getId() ?? 0)->setUserId($user->getId() ?? 0)->setStatus($status)->setSource(EnrollmentSource::SELF_SERVICE)->setActivatedAt(new DateTimeImmutable());
        $this->entityManager()->persist($enrollment);
        $this->entityManager()->flush();
        return $enrollment;
    }

    private function markLessonCompleted(EnrollmentEntity $enrollment, LessonEntity $lesson): void
    {
        $progress = (new LessonProgressEntity())
            ->setEnrollmentId($enrollment->getId() ?? 0)
            ->setLessonId($lesson->getId() ?? 0)
            ->setStatus(\Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus::COMPLETED)
            ->setStartedAt(new DateTimeImmutable('-1 hour'))
            ->setLastAccessedAt(new DateTimeImmutable('-10 minutes'))
            ->setCompletedAt(new DateTimeImmutable('-10 minutes'));
        $this->entityManager()->persist($progress);
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
