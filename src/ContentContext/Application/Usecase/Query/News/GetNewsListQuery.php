<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\News;

use Websymphonie\ContentContext\Domain\Enum\NewsStatus;

final class GetNewsListQuery
{
    public function __construct(public ?string $search = null, public ?NewsStatus $status = null, public int $page = 1, public int $limit = 20) {}
}
