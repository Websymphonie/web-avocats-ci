<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\Page;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\Page\GetPageListQuery;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Presenter\Form\Page\PageFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/pages', name: 'content_admin_page_')]
#[IsGranted('CONTENT_PAGE_VIEW')]
#[HasGroupAccess(RoleGroupEnum::PAGES)]
final class GetPageListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $query = new GetPageListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(PageFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_page_list')]);
        $form->handleRequest($request);
        $pages = $this->handleQuery(new GetPageListQuery($query->search ?: null, $query->status instanceof PageStatus ? $query->status : null, $query->page, 20));
        return $this->render('content/admin/page/index.html.twig', ['pages' => $pages, 'filterForm' => $form->createView(), 'mediaUrls' => $this->mediaUrls->resolveMany(array_values(array_filter(array_map(static fn ($page): ?int => $page->coverMediaId, $pages->items))))]);
    }
}
