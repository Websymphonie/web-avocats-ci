<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\CourseModule;

final readonly class DeleteCourseModuleCommand
{
    public function __construct(public int $trainingId, public int $id) {}
}
