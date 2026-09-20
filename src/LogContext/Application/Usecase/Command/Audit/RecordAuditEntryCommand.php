<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Command\Audit;

use DateTimeImmutable;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final class RecordAuditEntryCommand
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $context,
        public string $action,
        public AuditActorType $actorType,
        public ?string $actorId = null,
        public ?string $targetType = null,
        public ?string $targetId = null,
        public array $metadata = [],
        public ?DateTimeImmutable $occurredAt = null,
        public ?string $deduplicationKey = null,
    ) {
    }
}
