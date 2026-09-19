<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\LearningResourceFileServiceInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\RemoveLessonResourceCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class RemoveLessonResourceHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private LearningResourceFileServiceInterface $fileService, private CourseStructureGuard $guard) {}

    public function __invoke(RemoveLessonResourceCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = $this->lessonRepository->getByIdForModule($command->lessonId, $module->id);
        $resource = $this->resourceRepository->getByIdForLesson($command->resourceId, $lesson->id);
        $storedFileId = $resource->storedFileId;
        $this->resourceRepository->delete($resource);
        $this->fileService->deleteIfOrphaned($storedFileId);
        $this->resourceRepository->reorder($lesson->id, array_map(static fn ($item): int => $item->id, $this->resourceRepository->listByLesson($lesson->id)));
    }
}
