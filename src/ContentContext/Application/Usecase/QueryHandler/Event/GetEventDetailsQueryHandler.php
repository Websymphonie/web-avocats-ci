<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Event;

use Websymphonie\ContentContext\Application\Usecase\Query\Event\GetEventDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetEventDetailsQueryHandler implements QueryHandler
{
    public function __construct(private EventRepositoryInterface $repository) {}
    public function __invoke(GetEventDetailsQuery $query): Event { return $this->repository->getById($query->id); }
}
