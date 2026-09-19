<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Modules;

use Websymphonie\SharedContext\Application\Service\Sidebar\Enum\RouteEnum;
use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuFactory;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarModuleInterface;

final class LearningMenu implements SidebarModuleInterface
{
    public const string GROUP = 'Formations';

    public static function items(): array
    {
        return [
            MenuFactory::item(
                label: 'Formations',
                routes: [
                    RouteEnum::LEARNING_TRAINING_INDEX->value,
                    'learning_admin_training_new',
                    'learning_admin_training_show',
                    'learning_admin_training_edit',
                ],
                roles: [],
                icon: 'lucide:graduation-cap',
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Formations',
                        routes: [RouteEnum::LEARNING_TRAINING_INDEX->value, 'learning_admin_training_new', 'learning_admin_training_show', 'learning_admin_training_edit'],
                        roles: [],
                        link: RouteEnum::LEARNING_TRAINING_INDEX->value,
                        permission: 'LEARNING_TRAINING_VIEW',
                        order: 1,
                    ),
                ],
                groupOrder: 3,
                order: 1,
            ),
        ];
    }
}
