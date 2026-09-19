<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Command\Maintenance;

final class UpdateMaintenanceCommand
{
    public function __construct(
        public int  $id,
        public bool $active = false,
    )
    {
    }
}