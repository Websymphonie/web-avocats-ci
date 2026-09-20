<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\StartLessonCommand;
use Websymphonie\LearningContext\Domain\Model\LessonProgress;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class StartLessonHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private CourseModuleRepositoryInterface $modules, private LessonRepositoryInterface $lessons, private EnrollmentRepositoryInterface $enrollments, private LessonProgressRepositoryInterface $progress, private TrainingAccessPolicyInterface $policy, private CourseStructureGuard $guard) {}

    public function __invoke(StartLessonCommand $command): void
    {
        [$lesson, $enrollment] = $this->resolve($command->lessonUuid, $command->userId);
        $progress = $this->progress->findByEnrollmentAndLesson($enrollment->id, $lesson->id) ?? new LessonProgress(0, '', $enrollment->id, $lesson->id);
        $progress->start();
        $this->progress->save($progress);
    }

    /** @return array{0: \Websymphonie\LearningContext\Domain\Model\Lesson, 1: \Websymphonie\LearningContext\Domain\Model\Enrollment} */
    private function resolve(string $lessonUuid, int $userId): array
    {
        $lesson = $this->lessons->getByUuid($lessonUuid);
        $module = $this->modules->getById($lesson->moduleId);
        $training = $this->trainings->getById($module->trainingId);
        $this->guard->assertCourse($training);
        $this->policy->assertCanAccess($training->id, $userId);
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $userId);
        if ($enrollment === null) { throw new \LogicException('Une inscription active était attendue après le contrôle d’accès.'); }
        return [$lesson, $enrollment];
    }
}
