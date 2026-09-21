<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Usecase\QueryHandler;

use Websymphonie\LearningContext\Application\Usecase\Query\GetPublicTrainingBySlugQuery;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicTrainingBySlugHandler implements QueryHandler
{
    public function __construct(private TrainingRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublicTrainingBySlugQuery $query): Training
    {
        return $this->repository->getPublicBySlug($query->slug);
    }
}
