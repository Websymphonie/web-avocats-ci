<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Page;

use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPagesByGroupQuery;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class FindPublishedPagesByGroupQueryHandler implements QueryHandler
{
    public function __construct(private PageRepositoryInterface $repository) {}

    /** @return list<PublishedPage> */
    public function __invoke(FindPublishedPagesByGroupQuery $query): array
    {
        return array_map(
            static fn (Page $page): PublishedPage => new PublishedPage($page->uuid, $page->title, $page->slug, $page->content, $page->publishedAt, $page->coverMediaId, $page->group),
            $this->repository->findPublishedByGroup($query->group),
        );
    }
}
