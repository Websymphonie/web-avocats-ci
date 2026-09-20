<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\CourseModule;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\LearningResourceFileServiceInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\CourseModule\DeleteCourseModuleCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\LessonHasProgressException;
use Websymphonie\LearningContext\Domain\Repository\LessonProgressRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteCourseModuleHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $repository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private LearningResourceFileServiceInterface $fileService, private LessonProgressRepositoryInterface $progressRepository, private CourseStructureGuard $guard) {}

    public function __invoke(DeleteCourseModuleCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->repository->getByIdForTraining($command->id, $training->id);
        $lessons = $this->lessonRepository->listByModule($module->id);
        foreach ($lessons as $lesson) {
            if ($this->progressRepository->countByLesson($lesson->id) > 0) { throw new LessonHasProgressException(); }
        }
        foreach ($lessons as $lesson) {
            foreach ($this->resourceRepository->listByLesson($lesson->id) as $resource) { $this->resourceRepository->delete($resource); $this->fileService->deleteIfOrphaned($resource->storedFileId); }
            $this->lessonRepository->delete($lesson);
        }
        $this->repository->delete($module);
        $this->repository->reorder($training->id, array_map(static fn ($item): int => $item->id, $this->repository->listByTraining($training->id)));
    }
}
