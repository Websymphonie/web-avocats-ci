<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\RevokeTrainingAccessCommand;
use Websymphonie\LearningContext\Domain\Event\EnrollmentRevokedEvent;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\EnrollmentNotFoundException;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class RevokeTrainingAccessHandler implements CommandHandler
{
    public function __construct(private EnrollmentRepositoryInterface $enrollments, private TrainingRepositoryInterface $trainings, private EventDispatcher $eventDispatcher, private ?CurrentActorProvider $actorProvider = null) {}
    public function __invoke(RevokeTrainingAccessCommand $command): void
    {
        $enrollment = $this->enrollments->getById($command->enrollmentId);
        if ($enrollment->trainingId !== $command->trainingId) { throw EnrollmentNotFoundException::withId($command->enrollmentId); }
        if ($enrollment->isActive()) {
            $trainingTitle = $this->trainings->getById($enrollment->trainingId)->title;
            $enrollment->revoke();
            $enrollment = $this->enrollments->save($enrollment);
            $enrollment->emitEvent(new EnrollmentRevokedEvent($enrollment->uuid, $enrollment->userId, $enrollment->trainingId, $trainingTitle, $enrollment->revokedAt?->format('Y-m-d\\TH:i:s.uP') ?? $enrollment->uuid, $this->actorProvider?->currentUserId()));
            $this->eventDispatcher->dispatch($enrollment->releaseEvents());
        }
    }
}
