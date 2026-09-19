<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\ReorderLessonResourcesCommand;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReorderLessonResourcesHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private CourseStructureGuard $guard) {}

    public function __invoke(ReorderLessonResourcesCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = $this->lessonRepository->getByIdForModule($command->lessonId, $module->id);
        $expected = array_map(static fn ($resource): int => $resource->id, $this->resourceRepository->listByLesson($lesson->id));
        $actual = $command->resourceIds;
        if (count($actual) !== count(array_unique($actual)) || count($actual) !== count($expected) || array_diff($actual, $expected) !== [] || array_diff($expected, $actual) !== []) {
            throw new InvalidCourseStructureException('L’ordre des ressources est incomplet ou contient une ressource étrangère.');
        }
        $this->resourceRepository->reorder($lesson->id, $actual);
    }
}
