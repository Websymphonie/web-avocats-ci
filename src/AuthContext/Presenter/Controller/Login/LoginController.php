<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Login;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Websymphonie\IdentityContext\Domain\Service\User\UserRolesInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/login', name: 'app_login', methods: ['POST', 'GET'])]
final class LoginController extends AbstractController
{
    public function __construct(private readonly UserRolesInterface $roleResolver)
    {
    }

    public function __invoke(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            $role = $this->roleResolver->resolveMain($this->getUser()->getRoles());
            if ($role !== null) {
                return $this->redirectToRoute($role->route());
            }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        if ($lastUsername === '') {
            $lastUsername = (string)$request->query->get('email', '');
        }

        return $this->render('auths/login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
