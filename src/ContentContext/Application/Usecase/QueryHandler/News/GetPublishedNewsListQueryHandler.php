<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Query\News\GetPublishedNewsListQuery;
use Websymphonie\ContentContext\Domain\Model\NewsListResult;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublishedNewsListQueryHandler implements QueryHandler
{
    public function __construct(private NewsRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublishedNewsListQuery $query): NewsListResult
    {
        return $this->repository->listPublished(max(1, $query->page), max(1, $query->limit), $query->categoryId, $query->tagId);
    }
}
