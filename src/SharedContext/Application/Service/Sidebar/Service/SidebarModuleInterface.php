<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Sidebar\Service;

use Websymphonie\SharedContext\Application\Service\Sidebar\Model\MenuItem;

interface SidebarModuleInterface
{
    /**
     * Retourne les items de menu du module
     *
     * @return MenuItem[]
     */
    public static function items(): array;
}