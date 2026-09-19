<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\AuthLog;

final class GetAuthLogDetailsQuery
{
    public function __construct(public int $authLogId)
    {
    }
}