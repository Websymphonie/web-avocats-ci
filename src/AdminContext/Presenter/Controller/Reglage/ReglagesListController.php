<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Controller\Reglage;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglagePaginateListQuery;
use Websymphonie\AdminContext\Presenter\Form\Reglage\ReglageFilterType;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[Route(path: '/reglages/list', name: 'admin_reglages_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::REGLAGES)]
class ReglagesListController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request                     $request,
        BreadcrumsServiceInterface  $breadcrumsService,
        ContextServiceInterface     $contextService,
        #[MapQueryString]
        GetReglagePaginateListQuery $query
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $title = "Reglages de l'application";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('admin_reglages_index'));

        $form = $this->createForm(ReglageFilterType::class, $query);
        $form->handleRequest($request);
        /** @var PaginateListViewModel $reglages */
        $reglages = $this->handleQuery($query);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaginateListViewModel $reglages */
            $reglages = $this->handleQuery($query);
        }
        return $this->render('admin/reglage/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'form' => $form->createView(),
            'reglages' => $reglages,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
