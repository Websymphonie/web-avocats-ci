<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Model\Maintenance;

final class MaintenanceModel
{
    public function __construct(
        public ?int    $id = null,
        public ?string $uuid = null,
        public ?bool   $active = null,
    )
    {
    }
}
