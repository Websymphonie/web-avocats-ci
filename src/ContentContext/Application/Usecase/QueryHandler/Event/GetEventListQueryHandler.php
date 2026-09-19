<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetEventListQuery;
use Websymphonie\ContentContext\Domain\Model\EventListResult;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetEventListQueryHandler implements QueryHandler
{
    public function __construct(private EventRepositoryInterface $repository) {}
    public function __invoke(GetEventListQuery $query): EventListResult { return $this->repository->list($query->search, $query->status, $query->format, $query->categoryId, $query->tagId, $query->page, $query->limit); }
}
