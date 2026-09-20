<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

interface PaidTrainingAccessGranterInterface
{
    public function grant(int $userId, int $trainingId): void;
}
