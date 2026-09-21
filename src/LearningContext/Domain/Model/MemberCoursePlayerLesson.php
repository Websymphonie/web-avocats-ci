<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class MemberCoursePlayerLesson
{
    /** @param list<LessonResource> $resources */
    public function __construct(
        public Lesson $lesson,
        public ?LessonProgress $progress = null,
        public array $resources = [],
    ) {
    }

    public function isCompleted(): bool
    {
        return $this->progress?->isCompleted() ?? false;
    }

    public function isStarted(): bool
    {
        return $this->progress !== null;
    }
}
