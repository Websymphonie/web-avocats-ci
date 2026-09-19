<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetLessonDetailsQuery
{
    public function __construct(public int $trainingId, public int $moduleId, public int $lessonId) {}
}
