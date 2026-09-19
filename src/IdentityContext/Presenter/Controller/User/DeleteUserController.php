<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUserCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/users/{id}/delete', name: 'app_user_delete', requirements: ['id' => Requirement::DIGITS], methods: ['DELETE'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class DeleteUserController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[IsGranted(new Expression('is_granted("ROLE_DELETE")'))]
    public function __invoke(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $redirectUrl = SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_user_index')
        );

        if (!$this->isCsrfTokenValid('delete' . $id, $request->getPayload()->getString('_token'))) {
            $this->flash()->danger('Votre session a expiré. Rechargez la page puis réessayez.');

            return new RedirectResponse($redirectUrl);
        }

        try {
            $this->handleCommand(new DeleteUserCommand($id));
            $this->flash()->success('Utilisateur supprimé avec succès.');
        } catch (UserFacingError $e) {
            $this->flash()->errorFromException($e);
        }

        return new RedirectResponse($redirectUrl);
    }
}
