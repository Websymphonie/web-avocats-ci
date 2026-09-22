<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Batonnier;

use Websymphonie\ContentContext\Application\Usecase\Query\Batonnier\GetCurrentBatonnierMandateQuery;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCurrentBatonnierMandateQueryHandler implements QueryHandler
{
    public function __construct(private BatonnierMandateRepositoryInterface $repository)
    {
    }

    public function __invoke(GetCurrentBatonnierMandateQuery $query): ?BatonnierMandate
    {
        return $this->repository->findCurrent();
    }
}
