<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Role;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Query\Role\ListRolePermissionsQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/roles', name: 'app_role_permissions_index', methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::SUPER)]
#[IsGranted(new Expression('is_granted("ROLE_MANAGE")'))]
final class ListRolePermissionsController extends AbstractController
{
    /** @throws ContainerExceptionInterface|NotFoundExceptionInterface */
    public function __invoke(BreadcrumsServiceInterface $breadcrumbs): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $title = 'Rôles et permissions';
        $breadcrumbs
            ->addBreadcrumb('Utilisateurs', $this->generateUrl('app_user_index'))
            ->addBreadcrumb($title, $this->generateUrl('app_role_permissions_index'));
        return $this->render('identity/role/index.html.twig', [
            'title' => $title,
            'rolePermissions' => $this->handleQuery(new ListRolePermissionsQuery()),
            'breadcrumbs' => $breadcrumbs->getBreadcrumbs(),
        ]);
    }
}
