<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetPublishedEventBySlugQuery;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublishedEventBySlugQueryHandler implements QueryHandler
{
    public function __construct(private EventRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublishedEventBySlugQuery $query): Event
    {
        return $this->repository->getPublishedBySlug($query->slug);
    }
}
