<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\News;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\News\GetNewsListQuery;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Presenter\Form\News\NewsFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news', name: 'content_admin_news_')]
#[IsGranted('CONTENT_NEWS_VIEW')]
#[HasGroupAccess(RoleGroupEnum::NEWS)]
final class GetNewsListController extends AbstractController
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
        $query = new GetNewsListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(NewsFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_news_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        $result = $this->handleQuery(new GetNewsListQuery($query->search ?: null, $query->status instanceof NewsStatus ? $query->status : null, $query->page, $limit, $query->categoryId ?: null, $query->tagId ?: null));

        $mediaIds = array_values(array_filter(array_map(static fn ($item): ?int => $item->coverMediaId, $result->items)));

        return $this->render('content/admin/news/index.html.twig', [
            'news' => $result,
            'filterForm' => $form->createView(),
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }
}
