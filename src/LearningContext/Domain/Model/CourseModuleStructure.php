<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;

final class CourseModuleStructure
{
    /**
     * @param list<Lesson> $lessons
     * @param array<int, int> $resourceCounts
     * @param array<int, LessonProgressStatus> $progressStatuses
     */
    public function __construct(
        public readonly CourseModule $module,
        public readonly array $lessons,
        public readonly array $resourceCounts = [],
        public readonly array $progressStatuses = [],
    ) {}

    public function resourceCountFor(int $lessonId): int
    {
        return $this->resourceCounts[$lessonId] ?? 0;
    }

    public function progressStatusFor(int $lessonId): ?LessonProgressStatus
    {
        return $this->progressStatuses[$lessonId] ?? null;
    }
}
