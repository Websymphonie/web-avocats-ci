<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Modules;

use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuFactory;
use Websymphonie\SharedContext\Application\Service\Sidebar\Service\SidebarModuleInterface;

final class PaymentMenu implements SidebarModuleInterface
{
    public const string GROUP = 'Paiements';

    public static function items(): array
    {
        return [
            MenuFactory::item(
                label: 'Paiements',
                routes: [
                    'payment_admin_offer_list',
                    'payment_admin_offer_new',
                    'payment_admin_offer_edit',
                    'payment_admin_payment_list',
                    'payment_admin_payment_show',
                ],
                roles: [],
                icon: 'lucide:credit-card',
                group: self::GROUP,
                children: [
                    MenuFactory::item(
                        label: 'Tarifs des formations',
                        routes: ['payment_admin_offer_list', 'payment_admin_offer_new', 'payment_admin_offer_edit'],
                        roles: [],
                        link: 'payment_admin_offer_list',
                        permission: 'PAYMENT_OFFER_VIEW',
                        order: 1,
                    ),
                    MenuFactory::item(
                        label: 'Paiements',
                        routes: ['payment_admin_payment_list', 'payment_admin_payment_show'],
                        roles: [],
                        link: 'payment_admin_payment_list',
                        permission: 'PAYMENT_VIEW',
                        order: 2,
                    ),
                ],
                groupOrder: 4,
                order: 1,
            ),
        ];
    }
}
