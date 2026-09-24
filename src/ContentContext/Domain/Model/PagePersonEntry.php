<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

final readonly class PagePersonEntry
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $key,
        public string $displayName,
        public ?string $roleLabel,
        public ?string $periodLabel,
        public ?int $portraitMediaId,
        public ?string $linkUrl,
        public int $sortOrder,
    ) {
    }
}
