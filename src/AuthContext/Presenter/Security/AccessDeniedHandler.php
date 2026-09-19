<?php

declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class AccessDeniedHandler implements AccessDeniedHandlerInterface
{

    public function __construct(
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage
    )
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof UserInterface) {
            return new Response('Access denied.', Response::HTTP_FORBIDDEN);
        }
        $url = $this->router->generate('app_login');
        return new Response('', Response::HTTP_FOUND, ['Location' => $url]);
    }
}
