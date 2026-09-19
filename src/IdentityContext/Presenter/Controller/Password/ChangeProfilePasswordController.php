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
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeProfilePasswordCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\IdentityContext\Presenter\Form\Password\ProfileChangePasswordType;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/user/{id}/change-profile-password', name: 'app_user_profile_change_password', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ChangeProfilePasswordController extends AbstractController
{
    /**
     * @throws Throwable
     */
    #[IsGranted(new Expression('is_granted("ROLE_EDIT")'))]
    public function __invoke(Request $request, int $id, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $command = new ChangeProfilePasswordCommand(id: $id);
        $form = $this->createForm(ProfileChangePasswordType::class, $command);
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
            $this->generateUrl('app_user_profile_update', ['id' => $id])
        ));
    }
}
