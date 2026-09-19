<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Security;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarMenuService;

final readonly class AccessControlService
{
    public function __construct(
        private SidebarMenuService $sidebarMenuService
    )
    {
    }

    /** @param list<string> $userRoles */
    public function isRouteAllowed(array $userRoles, string $route, bool $throwException = true): bool
    {
        $menuItem = $this->sidebarMenuService->findMenuByRoute($route, $userRoles);

        if ($menuItem === null) {
            if ($throwException) {
                throw new AccessDeniedHttpException(
                    'Accès interdit : vous n\'avez pas les droits pour cette page.'
                );
            }

            return false;
        }

        if ($menuItem->allowAll || $menuItem->permission !== null || array_intersect($userRoles, $menuItem->roles)) {
            return true;
        }

        if ($throwException) {
            throw new AccessDeniedHttpException(
                'Accès interdit : vous n\'avez pas les droits pour cette page.'
            );
        }

        return false;
    }
}
