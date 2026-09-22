<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Page;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPagesByGroupQuery;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPageBySlugQuery;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

final class GetPublicPageDetailsController extends AbstractController
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
    #[Route('/informations/{slug}', name: 'web_information_detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        $page = $this->findPublishedPage($slug);
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        if ($page->group === PageGroup::BAR) {
            return $this->redirectToRoute('web_bar_page_detail', ['slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->renderPage($page);
    }

    #[Route('/le-barreau/{slug}', name: 'web_bar_page_detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function barPage(string $slug): Response
    {
        $page = $this->findPublishedPage($slug);
        if ($page === null || $page->group !== PageGroup::BAR) {
            throw $this->createNotFoundException();
        }

        return $this->renderPage($page);
    }

    private function findPublishedPage(string $slug): ?PublishedPage
    {
        /** @var PublishedPage|null $page */
        $page = $this->handleQuery(new FindPublishedPageBySlugQuery($slug));

        return $page;
    }

    private function renderPage(PublishedPage $page): Response
    {
        $coverUrl = null;
        if ($page->coverMediaId !== null) {
            $coverUrl = $this->mediaUrls->resolveMany([$page->coverMediaId])[$page->coverMediaId] ?? null;
        }

        return $this->render('web/pages/show.html.twig', [
            'page' => $page,
            'coverUrl' => $coverUrl,
            'safeContent' => $this->sanitizer->sanitize($page->content),
            'contextualPages' => $this->findContextualPages($page),
        ]);
    }

    /** @return list<PublishedPage> */
    private function findContextualPages(PublishedPage $page): array
    {
        if ($page->group === null) {
            return [];
        }

        $pages = $this->handleQuery(new FindPublishedPagesByGroupQuery($page->group));
        return count($pages) > 1 ? $pages : [];
    }
}
