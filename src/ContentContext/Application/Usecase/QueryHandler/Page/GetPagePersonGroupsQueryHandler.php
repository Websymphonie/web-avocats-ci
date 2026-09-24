<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Page;

use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPagePersonGroupsQuery;
use Websymphonie\ContentContext\Domain\Model\PagePersonGroup;
use Websymphonie\ContentContext\Domain\Repository\PagePersonGroupRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPagePersonGroupsQueryHandler implements QueryHandler
{
    public function __construct(private PagePersonGroupRepositoryInterface $repository)
    {
    }

    /** @return list<PagePersonGroup> */
    public function __invoke(GetPagePersonGroupsQuery $query): array
    {
        return $this->repository->listByPageId($query->pageId);
    }
}
