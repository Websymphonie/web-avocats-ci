<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo;

final readonly class GetPublishedEditorialVideoListQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 12,
    ) {
    }
}
