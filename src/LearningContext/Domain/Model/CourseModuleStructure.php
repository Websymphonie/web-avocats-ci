<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final class CourseModuleStructure
{
    /** @param list<Lesson> $lessons */
    public function __construct(
        public readonly CourseModule $module,
        public readonly array $lessons,
    ) {}
}
