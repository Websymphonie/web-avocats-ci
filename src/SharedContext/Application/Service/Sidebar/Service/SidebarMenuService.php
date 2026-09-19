<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuItem;

final readonly class SidebarMenuService
{
    /**
     * @param iterable<SidebarModuleInterface> $modules
     */
    public function __construct(
        private iterable $modules,
        private AuthorizationCheckerInterface $authorizationChecker,
    )
    {
    }

    /**
     * Récupère un item par sa route
     */
    /** @param list<string> $roles */
    public function findMenuByRoute(string $route, array $roles): ?MenuItem
    {
        foreach ($this->getMenuEntries($roles) as $items) {
            $match = $this->findInItems($items, $route);

            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Retourne les items filtrés par rôle et triés par groupe et ordre
     *
     * @param list<string> $roles
     * @return array<string, list<MenuItem>>
     */
    public function getMenuEntries(array $roles): array
    {
        $menuItems = [];

        // Récupère tous les items des modules
        foreach ($this->modules as $module) {
            $menuItems = array_merge($menuItems, $module::items());
        }

        // Filtrage et tri
        $filterAndSort = function (array $items) use ($roles, &$filterAndSort): array {
            $result = [];

            foreach ($items as $item) {
                $item->children = $filterAndSort($item->children);

                if ($item->isVisibleFor($roles, $this->authorizationChecker->isGranted(...))) {
                    // Tri des enfants
                    usort($item->children, fn(MenuItem $a, MenuItem $b) => $a->order <=> $b->order);
                    $result[] = $item;
                }
            }

            // Tri des items du même niveau par ordre
            usort($result, fn(MenuItem $a, MenuItem $b) => $a->order <=> $b->order);

            return $result;
        };

        $menuItems = $filterAndSort($menuItems);

        // Regroupement par groupe et tri des groupes par groupOrder
        $grouped = [];
        foreach ($menuItems as $item) {
            $group = $item->group ?? 'Autres';
            if (!isset($grouped[$group])) {
                $grouped[$group] = ['items' => [], 'groupOrder' => $item->groupOrder];
            }
            $grouped[$group]['items'][] = $item;
        }

        // Tri des groupes
        uasort($grouped, fn($a, $b) => $a['groupOrder'] <=> $b['groupOrder']);

        return array_map(function ($data) {
            return $data['items'];
        }, $grouped);
    }

    /**
     * @param MenuItem[] $items
     */
    private function findInItems(array $items, string $route): ?MenuItem
    {
        foreach ($items as $item) {
            if (in_array($route, $item->routes, true)) {
                return $item;
            }

            $childMatch = $this->findInItems($item->children, $route);
            if ($childMatch !== null) {
                return $childMatch;
            }
        }

        return null;
    }
}
