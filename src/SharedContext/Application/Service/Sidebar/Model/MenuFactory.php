<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Model;

use InvalidArgumentException;

final class MenuFactory
{
    /**
     * @param list<string> $routes
     * @param list<string> $roles
     * @param list<MenuItem> $children
     * @param array<string, scalar|null>|null $routeParams
     */
    public static function item(
        string  $label,
        array   $routes,
        array   $roles,
        ?string $icon = null,
        ?string $link = null,
        ?string $group = null,
        ?string $module = null,
        array   $children = [],
        bool    $isModal = false,
        ?string $modalId = null,
        ?string $modalComponent = null,
        ?string $modalTitle = null,
        ?string $modalDescription = null,
        string  $modalSize = 'md',
        bool    $disabled = false,
        ?array  $routeParams = null,
        ?string $badgeComponent = null,
        ?string $badgeClass = null,
        ?int    $groupOrder = 0,
        ?int    $order = 0,
        ?string $permission = null,
    ): MenuItem
    {
        if ($isModal && $modalId === null) {
            throw new InvalidArgumentException(
                sprintf('Le menu modal "%s" doit posséder un modalId.', $label),
            );
        }

        if ($isModal && $modalComponent === null) {
            throw new InvalidArgumentException(
                sprintf('Le menu modal "%s" doit posséder un modalComponent.', $label),
            );
        }

        return new MenuItem(
            label: $label,
            icon: $icon,
            link: $link,
            module: $module,
            routes: $routes,
            roles: $roles,
            allowAll: false,
            group: $group,
            isModal: $isModal,
            modalId: $modalId,
            modalComponent: $modalComponent,
            modalTitle: $modalTitle,
            modalDescription: $modalDescription,
            modalSize: $modalSize,
            disabled: $disabled,
            children: $children,
            routeParams: $routeParams,
            badgeComponent: $badgeComponent,
            badgeClass: $badgeClass,
            groupOrder: $groupOrder,
            order: $order,
            permission: $permission,
        );
    }
}
