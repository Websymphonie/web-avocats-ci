<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Model;

use DateTimeImmutable;

final class EditorialVideoCategory
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function update(string $name, string $slug, ?string $description): void
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }
}
