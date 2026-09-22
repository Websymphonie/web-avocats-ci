<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\EditorialVideo;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideo\GetPublishedEditorialVideoListQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\EditorialVideoCategory\GetEditorialVideoCategoryListQuery;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategoryListResult;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/videos', name: 'web_videos_')]
final class GetPublicEditorialVideoListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $categorySlug = trim((string) $request->query->get('category', ''));
        /** @var EditorialVideoCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetEditorialVideoCategoryListQuery(limit: 100));
        $selectedCategory = $this->findCategoryBySlug($categoryResult->items, $categorySlug);

        /** @var EditorialVideoListResult $videos */
        $videos = $this->handleQuery(new GetPublishedEditorialVideoListQuery(
            page: max(1, $request->query->getInt('page', 1)),
            limit: max(1, $context->getPaginatorPageSize()),
            category: $selectedCategory?->slug,
        ));

        $thumbnailUrls = [];
        foreach ($videos->items as $video) {
            if ($video->provider === VideoProvider::YOUTUBE && $video->externalVideoId !== null) {
                $thumbnailUrls[$video->id] = 'https://i.ytimg.com/vi/' . rawurlencode($video->externalVideoId) . '/hqdefault.jpg';
            }
        }

        return $this->render('web/videos/index.html.twig', [
            'videos' => $videos,
            'thumbnailUrls' => $thumbnailUrls,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /** @param list<EditorialVideoCategory> $categories */
    private function findCategoryBySlug(array $categories, string $slug): ?EditorialVideoCategory
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
