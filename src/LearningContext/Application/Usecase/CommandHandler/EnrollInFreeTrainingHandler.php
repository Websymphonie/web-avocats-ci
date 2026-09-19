<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
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
    public function __construct(private TrainingRepositoryInterface $trainings, private EnrollmentRepositoryInterface $enrollments, private UserDirectoryInterface $users) {}

    public function __invoke(EnrollInFreeTrainingCommand $command): void
    {
        $training = $this->trainings->getById($command->trainingId);
        $user = $this->users->getById($command->userId);
        if ($user === null || !$user->enabled || $training->status !== TrainingStatus::PUBLISHED || $training->accessType !== TrainingAccessType::FREE) { throw new TrainingEnrollmentDeniedException(); }
        $enrollment = $this->enrollments->findByTrainingAndUser($training->id, $command->userId);
        if ($enrollment === null) { $this->enrollments->save(new Enrollment(0, '', $training->id, $command->userId)); return; }
        if (!$enrollment->isActive()) { $enrollment->activate(EnrollmentSource::SELF_SERVICE); $this->enrollments->save($enrollment); }
    }
}
