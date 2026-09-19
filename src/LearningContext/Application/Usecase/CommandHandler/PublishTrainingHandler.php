<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\PublishTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class PublishTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository) {}
    public function __invoke(PublishTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        $unreadyLessonCount = 0;
        foreach ($this->moduleRepository->listByTraining($training->id) as $module) {
            foreach ($this->lessonRepository->listByModule($module->id) as $lesson) {
                if (!$lesson->isReadyForPublication($this->resourceRepository->countByLesson($lesson->id))) { ++$unreadyLessonCount; }
            }
        }
        $training->publish($this->moduleRepository->countByTraining($training->id), $this->moduleRepository->countEmptyByTraining($training->id), $unreadyLessonCount);
        $this->repository->save($training);
    }
}
