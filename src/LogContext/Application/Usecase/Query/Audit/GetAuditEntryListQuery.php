<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\Audit;

final class GetAuditEntryListQuery
{
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
        public ?string $context = null,
        public ?string $action = null,
        public ?string $actorId = null,
        public ?string $targetType = null,
        public ?string $targetId = null,
        public int $page = 1,
        public int $limit = 15,
    ) {
    }
}
