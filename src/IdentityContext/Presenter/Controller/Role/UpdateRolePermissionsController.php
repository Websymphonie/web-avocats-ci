<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Role;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Command\Role\UpdateRolePermissionsCommand;
use Websymphonie\IdentityContext\Application\Usecase\Query\Role\GetRolePermissionsQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Presenter\Form\Role\RolePermissionsFormType;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/roles/{role}', name: 'app_role_permissions_update', requirements: ['role' => 'ROLE_ADMIN|ROLE_AVOCAT|ROLE_USER'], methods: ['GET', 'POST'])]
#[HasGroupAccess(RoleGroupEnum::SUPER)]
#[IsGranted(new Expression('is_granted("ROLE_MANAGE")'))]
final class UpdateRolePermissionsController extends AbstractController
{
    /** @throws ContainerExceptionInterface|NotFoundExceptionInterface */
    public function __invoke(Request $request, string $role, BreadcrumsServiceInterface $breadcrumbs): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $userRole = UserRolesEnum::from($role);

        /** @var RolePermissions $rolePermissions */
        $rolePermissions = $this->handleQuery(new GetRolePermissionsQuery($userRole));
        $command = new UpdateRolePermissionsCommand($userRole, $rolePermissions->getPermissionCodes());
        $form = $this->createForm(RolePermissionsFormType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleCommand($command);
            $this->flash()->success(sprintf('Les permissions du rôle %s ont été mises à jour.', $userRole->label()));

            return $this->redirectToRoute('app_role_permissions_index');
        }

        $title = sprintf('Permissions : %s', $userRole->label());
        $breadcrumbs
            ->addBreadcrumb('Utilisateurs', $this->generateUrl('app_user_index'))
            ->addBreadcrumb('Rôles et permissions', $this->generateUrl('app_role_permissions_index'))
            ->addBreadcrumb($userRole->label(), $this->generateUrl('app_role_permissions_update', ['role' => $userRole->value]));

        return $this->render('identity/role/update.html.twig', [
            'title' => $title,
            'userRole' => $userRole,
            'rolePermissions' => $rolePermissions,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumbs->getBreadcrumbs(),
        ]);
    }
}
