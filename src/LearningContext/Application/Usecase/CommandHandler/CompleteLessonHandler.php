<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\CompleteLessonCommand;
use Websymphonie\LearningContext\Domain\Model\LessonProgress;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CompleteLessonHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private CourseModuleRepositoryInterface $modules, private LessonRepositoryInterface $lessons, private EnrollmentRepositoryInterface $enrollments, private LessonProgressRepositoryInterface $progress, private TrainingAccessPolicyInterface $policy, private CourseStructureGuard $guard) {}

    public function __invoke(CompleteLessonCommand $command): void
    {
        $lesson = $this->lessons->getByUuid($command->lessonUuid);
        $module = $this->modules->getById($lesson->moduleId);
        $training = $this->trainings->getById($module->trainingId);
        $this->guard->assertCourse($training);
        $this->policy->assertCanAccess($training->id, $command->userId);
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $command->userId);
        if ($enrollment === null) { throw new \LogicException('Une inscription active était attendue après le contrôle d’accès.'); }
        $progress = $this->progress->findByEnrollmentAndLesson($enrollment->id, $lesson->id) ?? new LessonProgress(0, '', $enrollment->id, $lesson->id);
        $progress->complete();
        $this->progress->save($progress);
    }
}
