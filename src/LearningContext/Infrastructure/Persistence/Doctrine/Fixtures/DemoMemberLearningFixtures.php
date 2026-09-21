<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Fixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress\LessonProgressEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;

final class DemoMemberLearningFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const EMAIL = 'avocat.demo@example.test';
    private const PASSWORD_ENV = 'APP_DEMO_MEMBER_PASSWORD';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getGroups(): array
    {
        return ['demo'];
    }

    public function getDependencies(): array
    {
        return [DemoLearningFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $plainPassword = getenv(self::PASSWORD_ENV);
        if (!is_string($plainPassword) || trim($plainPassword) === '') {
            throw new \LogicException(sprintf('La variable %s est requise pour charger le compte membre de démonstration.', self::PASSWORD_ENV));
        }

        DbLogListener::withoutLogging(function () use ($manager, $plainPassword): void {
            $user = $this->loadUser($manager, $plainPassword);
            $manager->flush();

            $enrollments = [
                'demo_training_course_01' => $this->loadEnrollment($manager, $user, 'demo_training_course_01'),
                'demo_training_course_02' => $this->loadEnrollment($manager, $user, 'demo_training_course_02'),
                'demo_training_course_03' => $this->loadEnrollment($manager, $user, 'demo_training_course_03'),
                'demo_training_live_01' => $this->loadEnrollment($manager, $user, 'demo_training_live_01'),
                'demo_training_live_02' => $this->loadEnrollment($manager, $user, 'demo_training_live_02'),
            ];
            $manager->flush();

            $this->loadProgress($manager, $enrollments['demo_training_course_02'], 'demo_training_course_02', 4, false);
            $this->loadProgress($manager, $enrollments['demo_training_course_03'], 'demo_training_course_03', 9, true);
            $manager->flush();

            $this->addReference('demo_member_avocat', $user);
        });
    }

    private function loadUser(ObjectManager $manager, string $plainPassword): User
    {
        $repository = $manager->getRepository(User::class);
        $user = $repository->findOneBy(['email' => self::EMAIL]);
        $isNew = !$user instanceof User;
        if ($isNew) {
            $user = new User();
        }

        $user->setName('Maître Awa Demo')
            ->setEmail(self::EMAIL)
            ->setRoles(['ROLE_AVOCAT']);
        $user->setEnabled(true);
        if ($isNew) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        }
        $manager->persist($user);

        return $user;
    }

    private function loadEnrollment(ObjectManager $manager, User $user, string $trainingReference): EnrollmentEntity
    {
        /** @var TrainingEntity $training */
        $training = $this->getReference($trainingReference, TrainingEntity::class);
        $repository = $manager->getRepository(EnrollmentEntity::class);
        $enrollment = $repository->findOneBy([
            'trainingId' => $training->getId(),
            'userId' => $user->getId(),
        ]);
        if (!$enrollment instanceof EnrollmentEntity) {
            $enrollment = new EnrollmentEntity();
        }

        $enrollment->setTrainingId($training->getId() ?? 0)
            ->setUserId($user->getId() ?? 0)
            ->setStatus(EnrollmentStatus::ACTIVE)
            ->setSource(EnrollmentSource::ADMIN_GRANT)
            ->setActivatedAt(new DateTimeImmutable('-7 days'))
            ->setRevokedAt(null);
        $manager->persist($enrollment);

        return $enrollment;
    }

    private function loadProgress(ObjectManager $manager, EnrollmentEntity $enrollment, string $trainingReference, int $completedLessons, bool $complete): void
    {
        /** @var TrainingEntity $training */
        $training = $this->getReference($trainingReference, TrainingEntity::class);
        $modules = $manager->getRepository(CourseModuleEntity::class)->findBy(
            ['trainingId' => $training->getId()],
            ['position' => 'ASC'],
        );
        $lessons = [];
        foreach ($modules as $module) {
            foreach ($manager->getRepository(LessonEntity::class)->findBy(['moduleId' => $module->getId()], ['position' => 'ASC']) as $lesson) {
                $lessons[] = $lesson;
            }
        }

        $progressCount = $complete ? count($lessons) : min(count($lessons), $completedLessons + 1);
        $progressRepository = $manager->getRepository(LessonProgressEntity::class);
        $now = new DateTimeImmutable('-2 days');
        for ($index = 0; $index < $progressCount; ++$index) {
            $lesson = $lessons[$index];
            $progress = $progressRepository->findOneBy([
                'enrollmentId' => $enrollment->getId(),
                'lessonId' => $lesson->getId(),
            ]);
            if (!$progress instanceof LessonProgressEntity) {
                $progress = new LessonProgressEntity();
            }

            $isCompleted = $complete || $index < $completedLessons;
            $progress->setEnrollmentId($enrollment->getId() ?? 0)
                ->setLessonId($lesson->getId() ?? 0)
                ->setStatus($isCompleted ? LessonProgressStatus::COMPLETED : LessonProgressStatus::IN_PROGRESS)
                ->setStartedAt($now->modify(sprintf('+%d hours', $index)))
                ->setLastAccessedAt($now->modify(sprintf('+%d hours', $index)))
                ->setCompletedAt($isCompleted ? $now->modify(sprintf('+%d hours', $index)) : null);
            $manager->persist($progress);
        }
    }

}
