<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Usecase\QueryHandler;

use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetDirectoryQuery;
use Websymphonie\LawyerContext\Domain\Model\PublicCabinetDirectoryEntry;
use Websymphonie\LawyerContext\Domain\Repository\LawyerDirectoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicCabinetDirectoryQueryHandler implements QueryHandler
{
    public function __construct(private LawyerDirectoryRepositoryInterface $repository)
    {
    }

    /** @return list<PublicCabinetDirectoryEntry> */
    public function __invoke(GetPublicCabinetDirectoryQuery $query): array
    {
        return $this->repository->listPublicCabinetsWithEligibleLawyers();
    }
}
