<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Application\Model;

final readonly class PublicSearchSuggestion
{
    public function __construct(
        public string $type,
        public string $title,
        public string $slug,
        public string $metadata,
    ) {
    }
}
