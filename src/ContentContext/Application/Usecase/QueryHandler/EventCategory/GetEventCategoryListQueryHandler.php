<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EventCategory;

use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryListQuery;
use Websymphonie\ContentContext\Domain\Model\EventCategoryListResult;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetEventCategoryListQueryHandler implements QueryHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository) {}
    public function __invoke(GetEventCategoryListQuery $query): EventCategoryListResult { return $this->repository->list($query->search, $query->page, $query->limit); }
}
