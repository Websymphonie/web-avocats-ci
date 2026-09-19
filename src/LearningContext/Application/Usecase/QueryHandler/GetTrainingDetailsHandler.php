<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingDetailsQuery;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetTrainingDetailsHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $repository) {}
    public function __invoke(GetTrainingDetailsQuery $query): Training
    {
        return $this->repository->getById($query->id);
    }
}
