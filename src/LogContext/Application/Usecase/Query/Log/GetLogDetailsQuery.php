<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\Log;

final class GetLogDetailsQuery
{
    public function __construct(public int $logId)
    {
    }
}