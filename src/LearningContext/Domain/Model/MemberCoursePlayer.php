<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class MemberCoursePlayer
{
    /**
     * @param list<MemberCoursePlayerModule> $modules
     */
    public function __construct(
        public Training $training,
        public ?TrainingCategory $category,
        public Enrollment $enrollment,
        public array $modules,
        public CourseProgress $progress,
        public MemberCoursePlayerLesson $activeLesson,
        public ?MemberCoursePlayerLesson $previousLesson,
        public ?MemberCoursePlayerLesson $nextLesson,
    ) {
    }
}
