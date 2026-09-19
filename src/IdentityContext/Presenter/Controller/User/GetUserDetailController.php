<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Query\User\GetUserDetailsQuery;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Exception\User\UserNotFoundException;
use Websymphonie\IdentityContext\Presenter\ViewModel\User\UserDetailViewModel;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/users/{id}/view', name: 'app_user_view', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class GetUserDetailController extends AbstractController
{

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_VIEW")'))]
    public function __invoke(
        int                        $id,
        BreadcrumsServiceInterface $breadcrumsService,
        ContextServiceInterface    $contextService,
        Request                    $request,
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            /** @var UserDetailViewModel $user */
            $user = $this->handleQuery(new GetUserDetailsQuery($id));
        } catch (UserNotFoundException) {
            throw $this->createNotFoundException();
        }
        $title = $user->object->name ?? $user->object->email ?? 'Utilisateur';
        $breadcrumsService->addBreadcrumb("Utilisateurs", $this->generateUrl('app_user_index'));
        $breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_user_update', ['id' => $id]));

        return $this->render('identity/user/view_profile.html.twig', [
            'title' => $title,
            'user' => $user->object,
            'breadcrumbs' => $breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
