<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\ReorderLessonsCommand;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReorderLessonsHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $repository, private CourseStructureGuard $guard) {}

    public function __invoke(ReorderLessonsCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        if (count($command->lessonIds) !== count(array_unique($command->lessonIds))) {
            throw new InvalidCourseStructureException('L’ordre des leçons contient des doublons.');
        }
        $lessons = $this->repository->listByModule($module->id);
        $knownIds = array_map(static fn ($lesson): int => $lesson->id, $lessons);
        if (count($knownIds) !== count($command->lessonIds) || array_diff($knownIds, $command->lessonIds) !== [] || array_diff($command->lessonIds, $knownIds) !== []) {
            throw new InvalidCourseStructureException('L’ordre des leçons est incomplet ou contient une leçon étrangère.');
        }
        $this->repository->reorder($module->id, $command->lessonIds);
    }
}
