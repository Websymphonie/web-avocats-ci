<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Exception\TrainingAccessDeniedException;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final readonly class TrainingAccessPolicy implements TrainingAccessPolicyInterface
{
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private TrainingLearnerEligibilityInterface $eligibility) {}

    public function canAccess(int $trainingId, int $userId): bool
    {
        if (!$this->eligibility->isEligible($userId)) { return false; }
        $training = $this->trainings->getById($trainingId);
        return $training->status === TrainingStatus::PUBLISHED && ($this->enrollments->findByTrainingAndUser($trainingId, $userId)?->isActive() ?? false);
    }

    public function assertCanAccess(int $trainingId, int $userId): void
    {
        if (!$this->canAccess($trainingId, $userId)) { throw new TrainingAccessDeniedException(); }
    }
}
