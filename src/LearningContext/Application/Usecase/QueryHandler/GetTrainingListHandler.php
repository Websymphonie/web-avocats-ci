<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Usecase\Query\GetTrainingListQuery;
use Websymphonie\LearningContext\Domain\Model\TrainingListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetTrainingListHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $repository) {}
    public function __invoke(GetTrainingListQuery $query): TrainingListResult
    {
        return $this->repository->list($query->search, $query->status, $query->visibility, $query->accessType, $query->type, max(1, $query->page), $query->limit);
    }
}
