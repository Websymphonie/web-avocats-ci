<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\ArchiveTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ArchiveTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository) {}
    public function __invoke(ArchiveTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        $training->archive();
        $this->repository->save($training);
    }
}
