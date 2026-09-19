<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EventCategory;

use Websymphonie\ContentContext\Application\Usecase\Query\EventCategory\GetEventCategoryDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetEventCategoryDetailsQueryHandler implements QueryHandler
{
    public function __construct(private EventCategoryRepositoryInterface $repository) {}
    public function __invoke(GetEventCategoryDetailsQuery $query): EventCategory { return $this->repository->getById($query->id); }
}
