<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

interface TrainingAccessPolicyInterface
{
    public function canAccess(int $trainingId, int $userId): bool;
    public function assertCanAccess(int $trainingId, int $userId): void;
}
