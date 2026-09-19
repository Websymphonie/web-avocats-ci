<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Query;

final readonly class GetAccessibleCourseStructureQuery
{
    public function __construct(public int $trainingId, public int $userId) {}
}
