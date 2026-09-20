<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

use Websymphonie\ContentContext\Domain\Enum\PageStatus;

final class GetPageListQuery
{
    public function __construct(
        public ?string $search = null,
        public ?PageStatus $status = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
