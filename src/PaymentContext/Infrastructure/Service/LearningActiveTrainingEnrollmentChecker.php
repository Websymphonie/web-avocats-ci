<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\PaymentContext\Application\Service\ActiveTrainingEnrollmentCheckerInterface;

final readonly class LearningActiveTrainingEnrollmentChecker implements ActiveTrainingEnrollmentCheckerInterface
{
    public function __construct(private EnrollmentRepositoryInterface $enrollments) {}

    public function hasActiveEnrollment(int $userId, int $trainingId): bool
    {
        return $this->enrollments->findByTrainingAndUser($trainingId, $userId)?->isActive() ?? false;
    }
}
