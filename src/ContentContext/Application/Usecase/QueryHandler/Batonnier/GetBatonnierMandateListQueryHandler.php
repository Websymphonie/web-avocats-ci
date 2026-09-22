<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Batonnier;

use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetBatonnierMandateListQuery;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetBatonnierMandateListQueryHandler implements QueryHandler
{
    public function __construct(private BatonnierMandateRepositoryInterface $repository)
    {
    }

    /** @return list<BatonnierMandate> */
    public function __invoke(GetBatonnierMandateListQuery $query): array
    {
        return $this->repository->list();
    }
}
