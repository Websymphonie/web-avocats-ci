<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Model;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;

final readonly class PublishedPage
{
    public function __construct(
        public string $uuid,
        public string $title,
        public string $slug,
        public string $content,
        public ?DateTimeImmutable $publishedAt,
        public ?int $coverMediaId,
        public ?PageGroup $group,
        public int $id = 0,
    ) {
    }
}
