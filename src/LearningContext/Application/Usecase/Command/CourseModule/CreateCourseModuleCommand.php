<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\CourseModule;

final class CreateCourseModuleCommand
{
    public function __construct(public int $trainingId, public string $title = '', public string $description = '') {}
}
