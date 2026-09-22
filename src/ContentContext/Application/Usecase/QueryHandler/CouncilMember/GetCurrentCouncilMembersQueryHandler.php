<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\CouncilMember;

use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCurrentCouncilMembersQuery;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCurrentCouncilMembersQueryHandler implements QueryHandler
{
    public function __construct(private CouncilMemberRepositoryInterface $repository)
    {
    }

    /** @return list<CouncilMember> */
    public function __invoke(GetCurrentCouncilMembersQuery $query): array
    {
        return $this->repository->listCurrent();
    }
}
