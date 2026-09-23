<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\QueryHandler;

use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerDirectoryQuery;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Domain\Repository\LawyerDirectoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicLawyerDirectoryQueryHandler implements QueryHandler
{
    public function __construct(private LawyerDirectoryRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublicLawyerDirectoryQuery $query): LawyerDirectoryResult
    {
        return $this->repository->listPublic(
            trim($query->name),
            trim($query->cabinet),
            trim($query->location),
            max(1, $query->page),
            max(1, $query->limit),
        );
    }
}
