<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Modules;

use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Enum\RouteEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuFactory;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarModuleInterface;

final class NotificationMenu implements SidebarModuleInterface
{
    const string GROUP = 'Alertes & notifications';

    public static function items(): array
    {
        return [
            MenuFactory::item(
                label: 'Notifications',
                routes: [RouteEnum::NOTIFICATION_INDEX->value, RouteEnum::NOTIFICATION_VIEW->value],
                roles: [...RoleGroupEnum::ALL->roles()],
                icon: 'carbon:notification',
                link: RouteEnum::NOTIFICATION_INDEX->value,
                group: self::GROUP,
                groupOrder: 4,
                order: 1
            ),
        ];
    }
}
