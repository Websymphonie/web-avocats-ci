<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

final readonly class CompleteLessonCommand
{
    public function __construct(public string $lessonUuid, public int $userId) {}
}
