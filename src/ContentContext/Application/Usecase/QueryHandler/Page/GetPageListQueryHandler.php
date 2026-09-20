<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Page;

use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageListQuery;
use Websymphonie\ContentContext\Domain\Model\PageListResult;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPageListQueryHandler implements QueryHandler
{
    public function __construct(private PageRepositoryInterface $repository) {}
    public function __invoke(GetPageListQuery $query): PageListResult { return $this->repository->list($query->search, $query->status, max(1, $query->page), $query->limit); }
}
