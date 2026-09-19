<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command\Lesson;

final readonly class UpdateLessonResourceCommand
{
    public function __construct(public int $trainingId, public int $moduleId, public int $lessonId, public int $resourceId, public string $title) {}
}
