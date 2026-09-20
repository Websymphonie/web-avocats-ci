<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Repository\Audit;

use DateTimeImmutable;

final readonly class AuditEntrySearchCriteria
{
    public function __construct(
        public int $page = 1,
        public int $limit = 15,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public ?string $context = null,
        public ?string $action = null,
        public ?string $actorId = null,
        public ?string $targetType = null,
        public ?string $targetId = null,
    ) {
    }
}
