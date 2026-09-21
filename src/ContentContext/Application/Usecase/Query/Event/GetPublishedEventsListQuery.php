<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Event;

final readonly class GetPublishedEventsListQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 15,
        public ?int $categoryId = null,
    ) {
    }
}
