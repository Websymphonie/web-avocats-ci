<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Controller\Password;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeUserPasswordCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\IdentityContext\Presenter\Form\Password\UsersChangePasswordType;
use Websymphonie\SharedContext\Infrastructure\Attribute\HasGroupAccess;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/user/{id}/change-password', name: 'app_user_change_password', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
#[HasGroupAccess(RoleGroupEnum::SUPER)]
final class ChangeUserPasswordController extends AbstractController
{
    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(Request $request, int $id, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = new ChangeUserPasswordCommand(id: $id);
        $form = $this->createForm(UsersChangePasswordType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                if ($this->getUser() instanceof User && $this->getUser()->getId() === $id) {
                    $request->getSession()->invalidate();
                    return $this->redirectToRoute('app_login');
                }
                $this->flash()->success('Mot de passe modifié avec succès.');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                $logger->error('ERROR DOMAIN USER PASSWORD UPDATE', ['exception' => $e]);
            }

        }
        return new RedirectResponse(SafeRedirectUrlResolver::resolve(
            $request,
            $this->generateUrl('app_user_index')
        ));
    }
}
