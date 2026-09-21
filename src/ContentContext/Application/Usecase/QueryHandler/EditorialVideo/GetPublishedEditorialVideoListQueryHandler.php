<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\EditorialVideo;

use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetPublishedEditorialVideoListQuery;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublishedEditorialVideoListQueryHandler implements QueryHandler
{
    public function __construct(private EditorialVideoRepositoryInterface $repository)
    {
    }

    public function __invoke(GetPublishedEditorialVideoListQuery $query): EditorialVideoListResult
    {
        return $this->repository->listPublished(max(1, $query->page), max(1, $query->limit));
    }
}
