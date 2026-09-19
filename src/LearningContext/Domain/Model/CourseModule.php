<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;

final class CourseModule
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $trainingId,
        public string $title,
        public string $description = '',
        public int $position = 1,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function update(string $title, string $description): void
    {
        $this->title = trim($title);
        $this->description = trim($description);
    }
}
