<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Query\Reglage;

final class GetReglagePaginateListQuery
{
    public function __construct(
        public ?string $value = null,
        public int     $page = 1,
        public int     $limit = 15,
    )
    {
    }
}