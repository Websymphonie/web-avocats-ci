<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\News;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetPublishedNewsBySlugQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory\GetNewsCategoryListQuery;
use Websymphonie\ContentContext\Domain\Exception\NewsNotFoundException;
use Websymphonie\ContentContext\Domain\Model\NewsCategoryListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/actualites', name: 'web_news_')]
final class GetPublicNewsDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface $sanitizer,
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{slug}', name: 'detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        try {
            $news = $this->handleQuery(new GetPublishedNewsBySlugQuery($slug));
        } catch (NewsNotFoundException) {
            throw $this->createNotFoundException();
        }

        $coverUrls = $news->coverMediaId !== null ? $this->mediaUrls->resolveMany([$news->coverMediaId]) : [];
        /** @var NewsCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetNewsCategoryListQuery(limit: 100));
        $selectedCategory = null;
        $currentCategory = $news->categories[0] ?? null;

        if ($currentCategory !== null) {
            foreach ($categoryResult->items as $category) {
                if ($category->id === $currentCategory->id) {
                    $selectedCategory = $category;
                    break;
                }
            }
        }

        return $this->render('web/news/show.html.twig', [
            'news' => $news,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'coverUrl' => $coverUrls[$news->coverMediaId] ?? null,
            'safeBody' => $this->sanitizer->sanitize($news->body),
        ]);
    }
}
