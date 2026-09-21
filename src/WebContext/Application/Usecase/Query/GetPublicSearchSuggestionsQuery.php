<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Application\Usecase\Query;

final readonly class GetPublicSearchSuggestionsQuery
{
    public function __construct(
        public string $term,
        public int $limitPerType = 4,
    ) {
    }
}
