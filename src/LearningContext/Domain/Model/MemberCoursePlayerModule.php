<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class MemberCoursePlayerModule
{
    /** @param list<MemberCoursePlayerLesson> $lessons */
    public function __construct(
        public CourseModule $module,
        public array $lessons,
    ) {
    }
}
