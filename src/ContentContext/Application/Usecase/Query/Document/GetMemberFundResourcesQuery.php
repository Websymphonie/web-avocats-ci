<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\Query\Document;

final readonly class GetMemberFundResourcesQuery
{
    public function __construct(
        public string $tagSlug,
        public int $page = 1,
        public int $limit = 15,
    ) {}
}
