<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;

final readonly class CourseProgress
{
    public function __construct(
        public int $enrollmentId,
        public int $totalLessons,
        public int $startedLessons,
        public int $completedLessons,
        public int $progressPercentage,
        public ?DateTimeImmutable $lastActivityAt,
    ) {}

    public static function empty(int $enrollmentId, int $totalLessons): self
    {
        return new self($enrollmentId, $totalLessons, 0, 0, 0, null);
    }
}
