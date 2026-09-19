<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\Lesson;

final readonly class ReorderLessonsCommand
{
    /** @param list<int> $lessonIds */
    public function __construct(public int $trainingId, public int $moduleId, public array $lessonIds) {}
}
