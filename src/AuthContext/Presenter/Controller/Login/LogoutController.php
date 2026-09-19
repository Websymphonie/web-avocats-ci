<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Controller\Login;

use LogicException;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\IdentityContext\Domain\Service\User\UserRolesInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route(path: '/logout', name: 'app_logout', methods: ['POST'])]
final class LogoutController extends AbstractController
{
    public function __invoke(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
