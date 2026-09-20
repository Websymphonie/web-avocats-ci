<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Event;

final readonly class EnrollmentRevokedEvent
{
    public function __construct(
        public string $enrollmentUuid,
        public int $userId,
        public int $trainingId,
        public string $trainingTitle,
        public string $revocationReference,
        public ?int $actorUserId = null,
    ) {
    }
}
