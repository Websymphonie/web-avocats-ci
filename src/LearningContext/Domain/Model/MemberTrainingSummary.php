<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class MemberTrainingSummary
{
    public function __construct(
        public Enrollment $enrollment,
        public Training $training,
        public ?TrainingCategory $category,
        public ?CourseProgress $progress,
    ) {
    }
}
