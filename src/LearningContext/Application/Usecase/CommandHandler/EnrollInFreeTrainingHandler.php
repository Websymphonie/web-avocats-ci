<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibilityInterface;
use Websymphonie\LearningContext\Application\Usecase\Command\EnrollInFreeTrainingCommand;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Exception\TrainingEnrollmentDeniedException;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class EnrollInFreeTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private TrainingLearnerEligibilityInterface $eligibility) {}

    public function __invoke(EnrollInFreeTrainingCommand $command): void
    {
        $training = $this->trainings->getById($command->trainingId);
        if (!$this->eligibility->isEligible($command->userId) || $training->status !== TrainingStatus::PUBLISHED || $training->accessType !== TrainingAccessType::FREE) { throw new TrainingEnrollmentDeniedException(); }
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $command->userId);
        if ($enrollment === null) { $this->enrollments->save(new Enrollment(0, '', $training->id, $command->userId)); return; }
        if (!$enrollment->isActive()) { $enrollment->activate(EnrollmentSource::SELF_SERVICE); $this->enrollments->save($enrollment); }
    }
}
