<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Activation;

use DateTimeImmutable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Application\Usecase\Command\Activation\ActivateAccountCommand;
use Websymphonie\IdentityContext\Domain\Exception\Activation\AccountActivationUnavailableException;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Presenter\Form\Activation\ActivateAccountType;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/account/activate/{selector}', name: 'app_account_activate', methods: ['GET', 'POST'])]
final class ActivateAccountController extends AbstractController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(Request $request, string $selector, AccountActivationRepositoryInterface $repository): Response
    {
        $token = (string)$request->query->get('token', '');
        $activation = $repository->findBySelector($selector);
        if ($activation === null || !$activation->matchesToken($token) || !$activation->isUsable(new DateTimeImmutable('now')) || $activation->getUser()->getEnabled() || $activation->getUser()->getAccountMustBeVerifedBefore() === null) {
            $this->flash()->danger('Ce lien d’activation est invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }
        $command = new ActivateAccountCommand($selector, $token);
        $form = $this->createForm(ActivateAccountType::class, $command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleCommand($command);
                $this->flash()->success('Votre compte est activé. Vous pouvez vous connecter.');
                return $this->redirectToRoute('app_login');
            } catch (AccountActivationUnavailableException) {
                $this->flash()->danger('Ce lien d’activation est invalide ou expiré.');
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('auths/activation/activation.html.twig', ['form' => $form->createView()]);
    }
}
