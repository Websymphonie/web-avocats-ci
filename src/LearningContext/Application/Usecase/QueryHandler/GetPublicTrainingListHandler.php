<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Usecase\Query\GetPublicTrainingListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Model\TrainingListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicTrainingListHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublicTrainingListQuery $query): TrainingListResult
    {
        return $this->repository->list(
            search: null,
            status: TrainingStatus::PUBLISHED,
            visibility: TrainingVisibility::PUBLIC,
            accessType: $query->accessType,
            type: $query->type,
            categoryId: $query->categoryId,
            tagId: null,
            page: max(1, $query->page),
            limit: $query->limit,
        );
    }
}
