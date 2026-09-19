<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class LessonDetails
{
    /** @param list<LessonResource> $resources */
    public function __construct(public CourseModule $module, public Lesson $lesson, public array $resources = [])
    {
    }
}
