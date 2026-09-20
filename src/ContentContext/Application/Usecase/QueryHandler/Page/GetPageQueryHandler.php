<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Page;

use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageQuery;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPageQueryHandler implements QueryHandler
{
    public function __construct(private PageRepositoryInterface $repository) {}
    public function __invoke(GetPageQuery $query): Page { return $this->repository->getById($query->id); }
}
