<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Event;

use DateTimeImmutable;

final readonly class TrainingLifecycleEvent
{
    public function __construct(
        public string $trainingUuid,
        public string $trainingType,
        public string $transition,
        public string $previousStatus,
        public string $newStatus,
        public string $title,
        public ?int $actorUserId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }
}
