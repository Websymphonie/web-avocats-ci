<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

final readonly class EnrollmentListItem
{
    public function __construct(public Enrollment $enrollment, public ?EnrollmentUser $user, public ?CourseProgress $progress = null) {}
}
