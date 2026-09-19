<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Reset;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\ResetPasswordCommand;
use Websymphonie\AuthContext\Domain\Repository\Reset\ResetPasswordRepositoryInterface;
use Websymphonie\AuthContext\Presenter\Form\ResetPassword\ResetPasswordType;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Service\Helper\BreadcrumsServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;
use Websymphonie\SharedContext\Presenter\Service\SafeRedirectUrlResolver;

#[Route('/password-reset')]
class PasswordResetController extends AbstractController
{
    public function __construct(private readonly BreadcrumsServiceInterface $breadcrumsService)
    {
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route(path: '/confirm/{selector}', name: 'password.reset', requirements: ['selector' => '[a-f0-9]{32}'], methods: ['GET', 'POST'])]
    public function __invoke(
        Request                          $request,
        string                           $selector,
        ResetPasswordRepositoryInterface $repository,
        \Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface $clock,
    ): Response
    {
        $title = "Changement de mot de passe";
        $this->breadcrumsService->addBreadcrumb($title, $this->generateUrl('app_login'));
        $secret = (string) $request->query->get('token', '');
        $resetPassword = $repository->getBySelector($selector);
        if ($resetPassword === null || !$resetPassword->matchesSecret($secret) || !$resetPassword->isUsable($clock->now()) || $resetPassword->getUser()?->getEnabled() !== true) {
            $this->flash()->danger('Lien de réinitialisation invalide ou expiré. Demandez-en un nouveau.');
            return $this->redirectToRoute('password.request');
        }

        $command = new ResetPasswordCommand(selector: $selector, secret: $secret);
        $form = $this->createForm(ResetPasswordType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand(command: $command);
                $this->flash()->success('Votre mot de passe a bien été réinitialisé.');
                return $this->redirectToRoute('app_login');
            } catch (UserFacingError $e) {
                $this->flash()->errorFromException($e);
                return $this->redirect(
                    SafeRedirectUrlResolver::resolve($request, $this->generateUrl('app_login'))
                );
            }
        }

        return $this->render('auths/reset_password/reset.html.twig', [
            'title' => $title,
            'resetForm' => $form,
            'breadcrumbs' => $this->breadcrumsService->getBreadcrumbs(),
        ]);
    }
}
