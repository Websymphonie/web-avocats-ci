<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\RevokeTrainingAccessCommand;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\EnrollmentNotFoundException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class RevokeTrainingAccessHandler implements CommandHandler
{
    public function __construct(private EnrollmentRepositoryInterface $enrollments) {}
    public function __invoke(RevokeTrainingAccessCommand $command): void
    {
        $enrollment = $this->enrollments->getById($command->enrollmentId);
        if ($enrollment->trainingId !== $command->trainingId) { throw EnrollmentNotFoundException::withId($command->enrollmentId); }
        if ($enrollment->isActive()) { $enrollment->revoke(); $this->enrollments->save($enrollment); }
    }
}
