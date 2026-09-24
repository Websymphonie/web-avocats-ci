<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Page;

final readonly class GetPagePersonGroupsQuery
{
    public function __construct(public int $pageId)
    {
    }
}
