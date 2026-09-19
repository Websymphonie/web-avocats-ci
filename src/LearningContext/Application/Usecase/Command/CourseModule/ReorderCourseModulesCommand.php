<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\CourseModule;

final readonly class ReorderCourseModulesCommand
{
    /** @param list<int> $moduleIds */
    public function __construct(public int $trainingId, public array $moduleIds) {}
}
