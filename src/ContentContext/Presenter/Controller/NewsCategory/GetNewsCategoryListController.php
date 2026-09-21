<?php
declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Controller\NewsCategory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Usecase\Query\NewsCategory\GetNewsCategoryListQuery;
use Websymphonie\ContentContext\Presenter\Form\NewsCategory\NewsCategoryFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/news-categories', name: 'content_admin_news_category_')]
#[IsGranted('CONTENT_NEWS_CATEGORY_VIEW')]
#[HasGroupAccess(RoleGroupEnum::CATEGORY_NEWS)]
final class GetNewsCategoryListController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $query = new GetNewsCategoryListQuery(page: max(1, $request->query->getInt('page', 1)));
        $form = $this->createForm(NewsCategoryFilterType::class, $query, ['method' => 'GET', 'action' => $this->generateUrl('content_admin_news_category_list')]);
        $form->handleRequest($request);
        $limit = $context->getPaginatorPageSize();
        return $this->render('content/admin/news_category/index.html.twig', ['categories' => $this->handleQuery(new GetNewsCategoryListQuery($query->search ?: null, $query->page, $limit)), 'filterForm' => $form->createView()]);
    }
}
