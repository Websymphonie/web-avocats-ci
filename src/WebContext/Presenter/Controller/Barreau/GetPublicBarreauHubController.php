<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Barreau;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\ContentContext\Application\Model\PublishedPage;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\FindPublishedPagesByGroupQuery;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/le-barreau', name: 'web_barreau_')]
final class GetPublicBarreauHubController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var list<PublishedPage> $pages */
        $pages = $this->handleQuery(new FindPublishedPagesByGroupQuery(PageGroup::BAR));

        $mediaIds = array_values(array_unique(array_filter(
            array_map(static fn (PublishedPage $page): ?int => $page->coverMediaId, $pages),
            static fn (?int $mediaId): bool => $mediaId !== null,
        )));

        return $this->render('web/barreau/index.html.twig', [
            'pages' => $pages,
            'coverUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }
}
