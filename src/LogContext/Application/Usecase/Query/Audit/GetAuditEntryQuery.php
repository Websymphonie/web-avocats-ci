<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\Audit;

final readonly class GetAuditEntryQuery
{
    public function __construct(public int $id)
    {
    }
}
