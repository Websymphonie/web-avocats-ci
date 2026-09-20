<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\ArchiveTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Event\TrainingLifecycleEvent;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class ArchiveTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private ?EventDispatcher $eventDispatcher = null, private ?CurrentActorProvider $actorProvider = null) {}
    public function __invoke(ArchiveTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        $previousStatus = $training->status->value;
        $training->archive();
        $training = $this->repository->save($training);
        $this->eventDispatcher?->dispatch([new TrainingLifecycleEvent($training->uuid, $training->type->value, 'ARCHIVED', $previousStatus, $training->status->value, $training->title, $this->actorProvider?->currentUserId())]);
    }
}
