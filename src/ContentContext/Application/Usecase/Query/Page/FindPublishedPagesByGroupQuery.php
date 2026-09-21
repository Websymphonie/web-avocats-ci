<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

use Websymphonie\ContentContext\Domain\Enum\PageGroup;

final readonly class FindPublishedPagesByGroupQuery
{
    public function __construct(public PageGroup $group) {}
}
