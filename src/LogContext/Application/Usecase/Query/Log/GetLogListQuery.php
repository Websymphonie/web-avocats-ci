<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\Query\Log;

final class GetLogListQuery
{
    public function __construct(
        public ?string $message = null,
        public ?string $levelName = null,
        public int     $page = 1,
        public int     $limit = 15,
    )
    {
    }
}