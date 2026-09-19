<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserListQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Presenter\Form\User\UserFilterType;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

#[Route('/users/list', name: 'app_user_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class GetUserListController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_LIST")'))]
    public function __invoke(
        Request                    $request,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface    $contextService,
        #[MapQueryString]
        GetUserListQuery           $query
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $query->limit = $contextService->getPaginatorPageSize();
        $query->page = $request->query->getInt('page', 1);
        $title = "Gestion des comptes";
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_user_index'));

        $form = $this->createForm(UserFilterType::class, $query);
        $form->handleRequest($request);

        /** @var PaginateListViewModel $users */
        $users = $this->handleQuery($query);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaginateListViewModel $users */
            $users = $this->handleQuery($query);
        }

        return $this->render('identity/user/index.html.twig', [
            'title' => $title,
            'query' => $query,
            'form' => $form->createView(),
            'users' => $users,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
