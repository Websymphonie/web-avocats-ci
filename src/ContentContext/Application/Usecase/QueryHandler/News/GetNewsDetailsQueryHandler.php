<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsDetailsQuery;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetNewsDetailsQueryHandler implements QueryHandler
{
    public function __construct(private NewsRepositoryInterface $repository) {}
    public function __invoke(GetNewsDetailsQuery $query): News { return $this->repository->getById($query->id); }
}
