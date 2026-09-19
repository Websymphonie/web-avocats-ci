<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\Lesson;

final class CreateLessonCommand
{
    public function __construct(public int $trainingId, public int $moduleId, public string $title = '', public ?string $summary = null) {}
}
