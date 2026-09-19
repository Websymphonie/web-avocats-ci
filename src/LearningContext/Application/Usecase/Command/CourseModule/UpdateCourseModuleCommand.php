<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\CourseModule;

final class UpdateCourseModuleCommand
{
    public function __construct(public int $trainingId, public int $id, public string $title = '', public string $description = '') {}
}
