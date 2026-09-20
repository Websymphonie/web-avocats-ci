<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Event;

final readonly class EnrollmentActivatedEvent
{
    public function __construct(
        public string $enrollmentUuid,
        public int $userId,
        public int $trainingId,
        public string $source,
        public string $trainingTitle,
        public string $activationReference,
        public ?int $actorUserId = null,
    ) {
    }
}
