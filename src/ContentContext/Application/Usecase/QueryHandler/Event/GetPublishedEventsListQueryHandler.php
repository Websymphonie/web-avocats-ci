<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetPublishedEventsListQuery;
use Websymphonie\ContentContext\Domain\Model\EventListResult;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublishedEventsListQueryHandler implements QueryHandler
{
    public function __construct(private EventRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublishedEventsListQuery $query): EventListResult
    {
        return $this->repository->listPublished(max(1, $query->page), max(1, $query->limit), $query->categoryId);
    }
}
