<?php
declare(strict_types=1);
namespace Websymphonie\IdentityContext\Presenter\Tiwg\Extension;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final class DashboardRouteExtension extends AbstractExtension
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dashboard_route', [$this, 'getDashboardRoute']),
        ];
    }

    public function getDashboardRoute(User $user): string
    {
        foreach ($user->getRoles() as $roleStr) {
            $roleEnum = UserRolesEnum::tryFrom($roleStr);
            if ($roleEnum && ($route = $roleEnum->route())) {
                return $this->router->generate($route);
            }
        }

        return "javascript:"; // Aucun rôle avec une route
    }
}
