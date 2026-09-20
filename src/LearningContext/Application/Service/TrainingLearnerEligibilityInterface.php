<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

interface TrainingLearnerEligibilityInterface
{
    public function isEligible(int $userId): bool;
}
