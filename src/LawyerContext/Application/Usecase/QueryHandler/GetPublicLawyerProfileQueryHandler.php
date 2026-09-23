<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\QueryHandler;

use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerProfileQuery;
use Websymphonie\LawyerContext\Domain\Model\LawyerPublicProfile;
use Websymphonie\LawyerContext\Domain\Repository\LawyerDirectoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicLawyerProfileQueryHandler implements QueryHandler
{
    public function __construct(private LawyerDirectoryRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublicLawyerProfileQuery $query): ?LawyerPublicProfile
    {
        return $this->repository->findPublicProfileByUuid($query->uuid);
    }
}
