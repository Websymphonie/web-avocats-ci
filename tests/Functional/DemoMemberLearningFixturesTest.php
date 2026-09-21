<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\Bundle\FixturesBundle\Loader\FixturesProvider;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress\LessonProgressEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoMemberLearningFixtures;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class DemoMemberLearningFixturesTest extends WebTestCase
{
    private static string $storageDirectory;
    private static string $demoPassword;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-demo-member-' . bin2hex(random_bytes(4));
        self::$demoPassword = bin2hex(random_bytes(16));
        mkdir(self::$storageDirectory, 0775, true);
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'APP_STORAGE_DIR' => self::$storageDirectory,
            'APP_DEMO_MEMBER_PASSWORD' => self::$demoPassword,
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
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
        putenv('APP_DEMO_MEMBER_PASSWORD');
        unset($_ENV['APP_DEMO_MEMBER_PASSWORD'], $_SERVER['APP_DEMO_MEMBER_PASSWORD']);
        parent::tearDownAfterClass();
    }

    public function testDemoAvocatScenarioContainsExpectedLearningState(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist((new Currencies())
            ->setCurrencyCode('XOF')
            ->setCurrencyName('Franc CFA')
            ->setDecimalPlace(0)
            ->setIsActive(true));
        $entityManager->flush();

        $loader = static::getContainer()->get('doctrine.fixtures.loader');
        self::assertInstanceOf(FixturesProvider::class, $loader);
        $fixtures = $loader->getFixtures(['demo']);
        (new ORMExecutor($entityManager, new ORMPurger($entityManager)))->execute($fixtures, true);

        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => DemoMemberLearningFixtures::EMAIL,
        ]);
        self::assertInstanceOf(User::class, $user);
        self::assertTrue($user->getEnabled());
        self::assertSame(['ROLE_AVOCAT'], $user->getRoles());
        self::assertNotSame(self::$demoPassword, $user->getPassword());

        $enrollments = $entityManager->getRepository(EnrollmentEntity::class)->findBy([
            'userId' => $user->getId(),
        ]);
        self::assertCount(5, $enrollments);
        foreach ($enrollments as $enrollment) {
            self::assertSame(EnrollmentStatus::ACTIVE, $enrollment->getStatus());
        }

        $course01 = $this->training($entityManager, 'cours-01-pratique-professionnelle');
        $course02 = $this->training($entityManager, 'cours-02-pratique-professionnelle');
        $course03 = $this->training($entityManager, 'cours-03-pratique-professionnelle');
        $live01 = $this->training($entityManager, 'live-01-rendez-vous-du-barreau');
        $live02 = $this->training($entityManager, 'live-02-rendez-vous-du-barreau');

        $course01Enrollment = $this->enrollment($entityManager, $user, $course01);
        $course02Enrollment = $this->enrollment($entityManager, $user, $course02);
        $course03Enrollment = $this->enrollment($entityManager, $user, $course03);
        $live01Enrollment = $this->enrollment($entityManager, $user, $live01);
        $live02Enrollment = $this->enrollment($entityManager, $user, $live02);

        self::assertCount(0, $this->progressFor($entityManager, $course01Enrollment));
        self::assertCount(5, $this->progressFor($entityManager, $course02Enrollment));
        self::assertSame(
            4,
            $this->progressCountByStatus($entityManager, $course02Enrollment, LessonProgressStatus::COMPLETED),
        );
        self::assertCount(9, $this->progressFor($entityManager, $course03Enrollment));
        self::assertSame(
            9,
            $this->progressCountByStatus($entityManager, $course03Enrollment, LessonProgressStatus::COMPLETED),
        );
        self::assertCount(0, $this->progressFor($entityManager, $live01Enrollment));
        self::assertCount(0, $this->progressFor($entityManager, $live02Enrollment));
        $course02Module = $entityManager->getRepository(CourseModuleEntity::class)->findOneBy([
            'trainingId' => $course02->getId(),
            'position' => 1,
        ]);
        self::assertInstanceOf(CourseModuleEntity::class, $course02Module);
        $youtubeLesson = $entityManager->getRepository(LessonEntity::class)->findOneBy([
            'moduleId' => $course02Module->getId(),
            'position' => 1,
        ]);
        self::assertInstanceOf(LessonEntity::class, $youtubeLesson);
        self::assertSame('YOUTUBE', $youtubeLesson->getVideoProvider());
        self::assertSame('M7lc1UVf-VE', $youtubeLesson->getExternalVideoId());
        self::assertSame('https://www.youtube.com/watch?v=M7lc1UVf-VE', $youtubeLesson->getVideoUrl());
        $liveDetails = $entityManager->getRepository(LiveTrainingDetailsEntity::class)->findOneBy([
            'trainingId' => $live01->getId(),
        ]);
        self::assertInstanceOf(LiveTrainingDetailsEntity::class, $liveDetails);
        self::assertGreaterThan(new \DateTimeImmutable(), $liveDetails->getStartsAt());
        self::assertNotNull($liveDetails->getJoinUrl());
        self::assertSame('YOUTUBE', $liveDetails->getStreamProvider()?->value);
        self::assertSame('M7lc1UVf-VE', $liveDetails->getExternalStreamId());
        self::assertCount(5, array_filter($enrollments, static fn (EnrollmentEntity $enrollment): bool =>
            in_array($enrollment->getTrainingId(), [
                $course01->getId(),
                $course02->getId(),
                $course03->getId(),
                $live01->getId(),
                $live02->getId(),
            ], true)
        ));
    }

    private function training(EntityManagerInterface $entityManager, string $slug): TrainingEntity
    {
        $training = $entityManager->getRepository(TrainingEntity::class)->findOneBy(['slug' => $slug]);
        self::assertInstanceOf(TrainingEntity::class, $training);

        return $training;
    }

    private function enrollment(EntityManagerInterface $entityManager, User $user, TrainingEntity $training): EnrollmentEntity
    {
        $enrollment = $entityManager->getRepository(EnrollmentEntity::class)->findOneBy([
            'userId' => $user->getId(),
            'trainingId' => $training->getId(),
        ]);
        self::assertInstanceOf(EnrollmentEntity::class, $enrollment);

        return $enrollment;
    }

    /** @return list<LessonProgressEntity> */
    private function progressFor(EntityManagerInterface $entityManager, EnrollmentEntity $enrollment): array
    {
        $progress = $entityManager->getRepository(LessonProgressEntity::class)->findBy([
            'enrollmentId' => $enrollment->getId(),
        ]);

        return array_values(array_filter($progress, static fn (LessonProgressEntity $item): bool => true));
    }

    private function progressCountByStatus(
        EntityManagerInterface $entityManager,
        EnrollmentEntity $enrollment,
        LessonProgressStatus $status,
    ): int {
        return count(array_filter(
            $this->progressFor($entityManager, $enrollment),
            static fn (LessonProgressEntity $progress): bool => $progress->getStatus() === $status,
        ));
    }
}
