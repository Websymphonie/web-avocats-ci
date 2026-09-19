<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\User;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\ResendAccountActivationCommand;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/users/{id}/activation/resend', name: 'app_user_activation_resend', methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::USER_ACCOUNT)]
final class ResendAccountActivationController extends AbstractController
{
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(Request $request, int $id): Response
    {
        if (!$this->isCsrfTokenValid('resend_account_activation', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $this->handleCommand(new ResendAccountActivationCommand($id));
        $this->flash()->success('Un nouveau lien d’activation a été envoyé.');
        return $this->redirectToRoute('app_user_view', ['id' => $id]);
    }
}
