<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibilityInterface;
use Websymphonie\PaymentContext\Application\Service\PaidTrainingBuyerEligibilityInterface;

final readonly class LearningPaidTrainingBuyerEligibility implements PaidTrainingBuyerEligibilityInterface
{
    public function __construct(private TrainingLearnerEligibilityInterface $eligibility)
    {
    }

    public function isEligible(int $userId): bool
    {
        return $this->eligibility->isEligible($userId);
    }
}
