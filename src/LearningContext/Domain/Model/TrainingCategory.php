<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;

final class TrainingCategory
{
    public function __construct(public readonly int $id, public readonly string $uuid, public string $name, public string $slug, public ?DateTimeImmutable $createdAt = null, public ?DateTimeImmutable $updatedAt = null) {}
    public function update(string $name, string $slug): void { $this->name = $name; $this->slug = $slug; }
}
