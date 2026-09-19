<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;

final class Enrollment
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $trainingId,
        public readonly int $userId,
        public EnrollmentStatus $status = EnrollmentStatus::ACTIVE,
        public EnrollmentSource $source = EnrollmentSource::SELF_SERVICE,
        public ?DateTimeImmutable $activatedAt = null,
        public ?DateTimeImmutable $revokedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function activate(EnrollmentSource $source, ?DateTimeImmutable $now = null): void
    {
        $this->status = EnrollmentStatus::ACTIVE;
        $this->source = $source;
        $this->activatedAt = $now ?? new DateTimeImmutable();
        $this->revokedAt = null;
    }

    public function revoke(?DateTimeImmutable $now = null): void
    {
        $this->status = EnrollmentStatus::REVOKED;
        $this->revokedAt = $now ?? new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === EnrollmentStatus::ACTIVE;
    }
}
