<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Page;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPageBySlugQuery;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/informations', name: 'web_information_')]
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
    #[Route('/{slug}', name: 'detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        /** @var PublishedPage|null $page */
        $page = $this->handleQuery(new FindPublishedPageBySlugQuery($slug));
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        $coverUrl = null;
        if ($page->coverMediaId !== null) {
            $coverUrl = $this->mediaUrls->resolveMany([$page->coverMediaId])[$page->coverMediaId] ?? null;
        }

        return $this->render('web/pages/show.html.twig', [
            'page' => $page,
            'coverUrl' => $coverUrl,
            'safeContent' => $this->sanitizer->sanitize($page->content),
        ]);
    }
}
