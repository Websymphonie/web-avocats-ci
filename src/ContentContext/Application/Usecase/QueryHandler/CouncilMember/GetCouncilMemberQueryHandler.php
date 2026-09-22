<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\CouncilMember;

use Websymphonie\ContentContext\Application\Usecase\Query\CouncilMember\GetCouncilMemberQuery;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetCouncilMemberQueryHandler implements QueryHandler
{
    public function __construct(private CouncilMemberRepositoryInterface $repository)
    {
    }

    public function __invoke(GetCouncilMemberQuery $query): CouncilMember
    {
        return $this->repository->getById($query->id);
    }
}
