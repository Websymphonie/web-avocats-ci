<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

final readonly class PagePersonGroup
{
    /** @param list<PagePersonEntry> $entries */
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public string $key,
        public int $sortOrder,
        public array $entries,
    ) {
    }
}
