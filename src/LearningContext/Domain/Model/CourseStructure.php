<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final class CourseStructure
{
    /** @param list<CourseModuleStructure> $modules */
    public function __construct(
        public readonly Training $training,
        public readonly array $modules,
    ) {}
}
