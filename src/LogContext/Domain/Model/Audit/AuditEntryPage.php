<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Audit;

final readonly class AuditEntryPage
{
    /** @param list<AuditEntry> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }
}
