<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\BulkDeleteTrainingsCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\TrainingHasEnrollmentsException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteTrainingsHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private EnrollmentRepositoryInterface $enrollments) {}
    public function __invoke(BulkDeleteTrainingsCommand $command): void
    {
        foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $training) {
            if ($this->enrollments->countByTraining($training->id) > 0) { throw new TrainingHasEnrollmentsException(); }
            $this->repository->delete($training);
        }
    }
}
