<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\Command;

final readonly class EnrollInFreeTrainingCommand
{
    public function __construct(public int $trainingId, public int $userId) {}
}
