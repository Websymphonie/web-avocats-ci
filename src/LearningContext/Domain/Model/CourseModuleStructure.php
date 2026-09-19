<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final class CourseModuleStructure
{
    /**
     * @param list<Lesson> $lessons
     * @param array<int, int> $resourceCounts
     */
    public function __construct(
        public readonly CourseModule $module,
        public readonly array $lessons,
        public readonly array $resourceCounts = [],
    ) {}

    public function resourceCountFor(int $lessonId): int
    {
        return $this->resourceCounts[$lessonId] ?? 0;
    }
}
