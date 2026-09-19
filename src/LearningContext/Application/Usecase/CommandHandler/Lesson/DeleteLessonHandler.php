<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\LearningResourceFileServiceInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\DeleteLessonCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteLessonHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $repository, private LessonResourceRepositoryInterface $resourceRepository, private LearningResourceFileServiceInterface $fileService, private CourseStructureGuard $guard) {}

    public function __invoke(DeleteLessonCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = $this->repository->getByIdForModule($command->id, $module->id);
        foreach ($this->resourceRepository->listByLesson($lesson->id) as $resource) { $this->resourceRepository->delete($resource); $this->fileService->deleteIfOrphaned($resource->storedFileId); }
        $this->repository->delete($lesson);
        $this->repository->reorder($module->id, array_map(static fn ($item): int => $item->id, $this->repository->listByModule($module->id)));
    }
}
