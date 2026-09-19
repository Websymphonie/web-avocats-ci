<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\DeleteTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\TrainingHasEnrollmentsException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private EnrollmentRepositoryInterface $enrollments) {}
    public function __invoke(DeleteTrainingCommand $command): void
    {
        if ($this->enrollments->countByTraining($command->id) > 0) { throw new TrainingHasEnrollmentsException(); }
        $this->repository->delete($this->repository->getById($command->id));
    }
}
