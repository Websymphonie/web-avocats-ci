<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Twig\Sidebar;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarMenuService;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuItem;

final class SidebarMenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly SidebarMenuService $sidebarMenuService,
        private readonly RoleHierarchyInterface $roleHierarchy,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sidebar_menu_entries', [$this, 'getSidebarMenuEntries']),
        ];
    }

    /**
     * @param list<string> $roles
     * @return array<string, list<MenuItem>>
     */
    public function getSidebarMenuEntries(array $roles): array
    {
        return $this->sidebarMenuService->getMenuEntries(
            $this->roleHierarchy->getReachableRoleNames($roles),
        );
    }
}
