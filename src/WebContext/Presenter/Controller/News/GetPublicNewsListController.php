<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\News;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetPublishedNewsListQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory\GetNewsCategoryListQuery;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\NewsCategoryListResult;
use Websymphonie\ContentContext\Domain\Model\NewsListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/actualites', name: 'web_news_')]
final class GetPublicNewsListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $categorySlug = trim((string) $request->query->get('categorie', ''));
        /** @var NewsCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetNewsCategoryListQuery(limit: 100));
        $selectedCategory = $this->findCategoryBySlug($categoryResult->items, $categorySlug);

        /** @var NewsListResult $news */
        $news = $this->handleQuery(new GetPublishedNewsListQuery(
            page: max(1, $request->query->getInt('page', 1)),
            limit: max(1, $context->getPaginatorPageSize()),
            categoryId: $selectedCategory?->id,
        ));

        $mediaIds = array_values(array_filter(array_map(static fn ($item): ?int => $item->coverMediaId, $news->items)));

        return $this->render('web/news/index.html.twig', [
            'news' => $news,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }

    /** @param list<NewsCategory> $categories */
    private function findCategoryBySlug(array $categories, string $slug): ?NewsCategory
    {
        if ($slug === '') {
            return null;
        }

        foreach ($categories as $category) {
            if ($category->slug === $slug) {
                return $category;
            }
        }

        return null;
    }
}
