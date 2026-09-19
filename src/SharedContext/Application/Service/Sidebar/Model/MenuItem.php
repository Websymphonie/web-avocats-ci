<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Model;

class MenuItem
{
    /**
     * @param list<string> $routes
     * @param list<string> $roles
     * @param list<MenuItem> $children
     * @param array<string, scalar|null>|null $routeParams
     */
    public function __construct(
        public string           $label,
        public ?string          $icon = null,
        public ?string          $link = null,
        public ?string          $module = null,
        public array            $routes = [],
        public array            $roles = [],
        public bool             $allowAll = false,
        public ?string          $group = null,
        public bool             $isModal = false,
        public readonly ?string $modalId = null,
        public readonly ?string $modalComponent = null,
        public readonly ?string $modalTitle = null,
        public readonly ?string $modalDescription = null,
        public readonly string  $modalSize = 'md',
        public bool             $disabled = false,
        public array            $children = [],
        public ?array           $routeParams = null,
        public ?string          $badgeComponent = null,
        public ?string          $badgeClass = null,
        public int              $groupOrder = 0, // ordre du groupe
        public int              $order = 0,      // ordre de l’item dans le groupe
        public ?string          $permission = null,
    )
    {
    }

    /** @param list<string> $userRoles */
    public function isVisibleFor(array $userRoles, ?callable $permissionChecker = null): bool
    {
        if ($this->allowAll) {
            return true;
        }

        if ($this->permission !== null && $permissionChecker !== null) {
            return (bool) $permissionChecker($this->permission);
        }

        return count(array_intersect($userRoles, $this->roles)) > 0;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function isActive(string $currentRoute): bool
    {
        if (in_array($currentRoute, $this->routes, true)) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->isActive($currentRoute)) {
                return true;
            }
        }

        return false;
    }

    public function hasModal(): bool
    {
        return $this->isModal
            && $this->modalId !== null
            && $this->modalComponent !== null;
    }
}
