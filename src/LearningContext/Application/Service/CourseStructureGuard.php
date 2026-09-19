<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Model\Training;

final class CourseStructureGuard
{
    public function assertCourse(Training $training): void
    {
        if ($training->type !== TrainingType::COURSE) {
            throw new InvalidCourseStructureException('Les modules et les leçons sont réservés aux formations de type cours.');
        }
    }
}
