<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Application\Usecase\Query\Maintenance;

final class GetMaintenanceDetailsQuery
{
    public function __construct(public int $id)
    {
    }
}