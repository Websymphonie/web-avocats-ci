<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\AuthLog;

final class GetAuthLogListQuery
{
    public function __construct(
        public ?string $userIp = null,
        public ?string $emailEntered = null,
        public int     $page = 1,
        public int     $limit = 15,
    )
    {
    }
}