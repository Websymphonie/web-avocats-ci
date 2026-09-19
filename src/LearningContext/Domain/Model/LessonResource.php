<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;

final class LessonResource
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $lessonId,
        public readonly int $storedFileId,
        public string $title,
        public int $position = 1,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public readonly string $originalName = '',
        public readonly string $mimeType = '',
        public readonly int $size = 0,
    ) {
    }

    public function rename(string $title): void
    {
        $title = trim($title);
        if ($title === '') { throw new InvalidCourseStructureException('Une ressource doit avoir un nom.'); }
        $this->title = $title;
    }
}
