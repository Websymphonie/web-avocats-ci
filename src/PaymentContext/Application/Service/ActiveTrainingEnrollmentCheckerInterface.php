<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

interface ActiveTrainingEnrollmentCheckerInterface
{
    public function hasActiveEnrollment(int $userId, int $trainingId): bool;
}
