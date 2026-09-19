<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler\Lesson;

use Websymphonie\LearningContext\Application\Service\CourseStructureGuard;
use Websymphonie\LearningContext\Application\Service\LearningResourceFileServiceInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\Lesson\AddLessonResourcesCommand;
use Websymphonie\LearningContext\Domain\Model\LessonResource;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class AddLessonResourcesHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainingRepository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private LearningResourceFileServiceInterface $fileService, private CourseStructureGuard $guard) {}

    public function __invoke(AddLessonResourcesCommand $command): void
    {
        $training = $this->trainingRepository->getById($command->trainingId);
        $this->guard->assertCourse($training);
        $module = $this->moduleRepository->getByIdForTraining($command->moduleId, $training->id);
        $lesson = $this->lessonRepository->getByIdForModule($command->lessonId, $module->id);
        $position = $this->resourceRepository->countByLesson($lesson->id) + 1;
        $created = [];
        $storedFileIds = [];
        try {
            foreach ($command->files as $file) {
                $stored = $this->fileService->upload($file);
                $storedFileIds[] = $stored->id;
                $resource = new LessonResource(0, '', $lesson->id, $stored->id, $stored->originalName, $position++);
                $created[] = $this->resourceRepository->save($resource);
            }
        } catch (\Throwable $exception) {
            foreach ($created as $resource) { $this->resourceRepository->delete($resource); }
            foreach (array_unique($storedFileIds) as $storedFileId) { $this->fileService->deleteIfOrphaned($storedFileId); }
            throw $exception;
        }
    }
}
