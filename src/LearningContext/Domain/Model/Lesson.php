<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;

final class Lesson
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $moduleId,
        public string $title,
        public ?string $summary = null,
        public int $position = 1,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function update(string $title, ?string $summary): void
    {
        $this->title = trim($title);
        $this->summary = $summary !== null && trim($summary) !== '' ? trim($summary) : null;
    }
}
