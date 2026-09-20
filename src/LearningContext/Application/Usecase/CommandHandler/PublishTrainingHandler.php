<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\PublishTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Domain\Event\TrainingLifecycleEvent;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class PublishTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private CourseModuleRepositoryInterface $moduleRepository, private LessonRepositoryInterface $lessonRepository, private LessonResourceRepositoryInterface $resourceRepository, private ?EventDispatcher $eventDispatcher = null, private ?CurrentActorProvider $actorProvider = null) {}
    public function __invoke(PublishTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        $unreadyLessonCount = 0;
        foreach ($this->moduleRepository->listByTraining($training->id) as $module) {
            foreach ($this->lessonRepository->listByModule($module->id) as $lesson) {
                if (!$lesson->isReadyForPublication($this->resourceRepository->countByLesson($lesson->id))) { ++$unreadyLessonCount; }
            }
        }
        $previousStatus = $training->status->value;
        $training->publish($this->moduleRepository->countByTraining($training->id), $this->moduleRepository->countEmptyByTraining($training->id), $unreadyLessonCount);
        $training = $this->repository->save($training);
        $this->eventDispatcher?->dispatch([new TrainingLifecycleEvent($training->uuid, $training->type->value, 'PUBLISHED', $previousStatus, $training->status->value, $training->title, $this->actorProvider?->currentUserId(), $training->publishedAt ?? new \DateTimeImmutable())]);
    }
}
