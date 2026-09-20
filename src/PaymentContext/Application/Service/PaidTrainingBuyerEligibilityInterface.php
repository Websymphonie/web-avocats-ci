<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

interface PaidTrainingBuyerEligibilityInterface
{
    public function isEligible(int $userId): bool;
}
