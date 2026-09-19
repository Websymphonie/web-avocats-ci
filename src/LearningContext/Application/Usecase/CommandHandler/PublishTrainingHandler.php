<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\CommandHandler;

use Websymphonie\LearningContext\Application\Usecase\Command\PublishTrainingCommand;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class PublishTrainingHandler implements CommandHandler
{
    public function __construct(private TrainingRepositoryInterface $repository) {}
    public function __invoke(PublishTrainingCommand $command): void
    {
        $training = $this->repository->getById($command->id);
        $training->publish();
        $this->repository->save($training);
    }
}
