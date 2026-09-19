<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\BulkDeleteTrainingsCommand;
use Websymphonie\LearningContext\Application\Service\TrainingStructureDeletion;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Exception\TrainingHasEnrollmentsException;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class BulkDeleteTrainingsHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository, private EnrollmentRepositoryInterface $enrollments, private TrainingStructureDeletion $structureDeletion) {}
    public function __invoke(BulkDeleteTrainingsCommand $command): void
    {
        $trainings = $this->repository->findByIds(array_values(array_unique($command->ids)));
        foreach ($trainings as $training) {
            if ($this->enrollments->countByTraining($training->id) > 0) {
                throw new TrainingHasEnrollmentsException('Une ou plusieurs formations possèdent des inscriptions et ne peuvent pas être supprimées.');
            }
        }

        foreach ($trainings as $training) {
            $this->structureDeletion->deleteFor($training);
            $this->repository->delete($training);
        }
    }
}
