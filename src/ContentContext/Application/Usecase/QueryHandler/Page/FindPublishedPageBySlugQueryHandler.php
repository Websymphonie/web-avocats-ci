<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\QueryHandler\Page;

use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPageBySlugQuery;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class FindPublishedPageBySlugQueryHandler implements QueryHandler
{
    public function __construct(private PageRepositoryInterface $repository) {}

    public function __invoke(FindPublishedPageBySlugQuery $query): ?PublishedPage
    {
        $page = $this->repository->findPublishedBySlug(trim($query->slug));
        return $page === null ? null : new PublishedPage($page->uuid, $page->title, $page->slug, $page->content, $page->publishedAt);
    }
}
