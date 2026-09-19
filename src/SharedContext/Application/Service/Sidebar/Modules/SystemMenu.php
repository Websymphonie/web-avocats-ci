<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Modules;

use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Enum\RouteEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuFactory;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarModuleInterface;

final class SystemMenu implements SidebarModuleInterface
{
    const string GROUP = 'Système & Reglages';

    public static function items(): array
    {
        return [
            MenuFactory::item(
                label: 'Gestion des utilisateurs',
                routes: [
                    RouteEnum::USER_INDEX->value,
                    RouteEnum::USER_ADD->value,
                    RouteEnum::USER_UPDATE->value,
                    RouteEnum::USER_PROFILE_UPDATE->value,
                    RouteEnum::USER_VIEW->value,
                ],
                roles: RoleGroupEnum::USER_ACCOUNT->roles(),
                icon: 'heroicons:users',
                link: RouteEnum::USER_INDEX->value,
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Liste des utilisateurs',
                        routes: [RouteEnum::USER_INDEX->value],
                        roles: RoleGroupEnum::USER_ACCOUNT->roles(),
                        link: RouteEnum::USER_INDEX->value,
                        order: 1
                    ),
                ],
                groupOrder: 4,
                order: 2,
            ),
            MenuFactory::item(
                label: 'Configuration système',
                routes: [
                    RouteEnum::REGLAGE_INDEX->value,
                    RouteEnum::IMAGE_INDEX->value,
                ],
                roles: RoleGroupEnum::USER_ACCOUNT->roles(),
                icon: 'heroicons:users',
                link: RouteEnum::USER_INDEX->value,
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Réglages application',
                        routes: [RouteEnum::REGLAGE_INDEX->value],
                        roles: RoleGroupEnum::REGLAGES->roles(),
                        link: RouteEnum::REGLAGE_INDEX->value,
                        order: 1
                    ),
                    MenuFactory::item(
                        label: 'Gestion des images',
                        routes: [RouteEnum::IMAGE_INDEX->value],
                        roles: RoleGroupEnum::IMAGES->roles(),
                        link: RouteEnum::IMAGE_INDEX->value,
                        order: 2
                    ),
                ],
                groupOrder: 4,
                order: 3,
            ),
            MenuFactory::item(
                label: 'Activités & Logs',
                routes: [
                    RouteEnum::AUTHLOG_INDEX->value,
                    RouteEnum::LOG_INDEX->value,
                ],
                roles: RoleGroupEnum::USER_ACCOUNT->roles(),
                icon: 'heroicons:users',
                link: RouteEnum::USER_INDEX->value,
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Historique connexions',
                        routes: [RouteEnum::AUTHLOG_INDEX->value],
                        roles: RoleGroupEnum::LOGS->roles(),
                        link: RouteEnum::AUTHLOG_INDEX->value,
                        order: 1
                    ),
                    MenuFactory::item(
                        label: 'Gestion des activités',
                        routes: [RouteEnum::LOG_INDEX->value],
                        roles: RoleGroupEnum::LOGS->roles(),
                        link: RouteEnum::LOG_INDEX->value,
                        order: 2
                    ),
                ],
                groupOrder: 4,
                order: 4,
            ),
            MenuFactory::item(
                label: 'Mode maintenance',
                routes: [RouteEnum::MAINTENANCE_INDEX->value],
                roles: RoleGroupEnum::MAINTENANCE->roles(),
                icon: 'material-symbols:rule-settings',
                group: self::GROUP,
                isModal: true,
                modalId: 'modal-maintenance-edit',
                modalComponent: 'MaintenancesModalFormComponent',
                modalTitle: 'Mode maintenance',
                modalDescription: 'Activez ou désactivez temporairement le mode maintenance de l’application.',
                groupOrder: 4,
                order: 5
            ),
        ];
    }
}
