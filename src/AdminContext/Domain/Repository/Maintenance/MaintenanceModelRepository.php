<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Repository\Maintenance;

use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance\Maintenances;

interface MaintenanceModelRepository
{
    public function create(Maintenances $entity): MaintenanceModel;

    public function update(Maintenances $entity): MaintenanceModel;

    public function remove(Maintenances $entity): void;

    public function getById(int $id): ?MaintenanceModel;

    public function getByEntityId(int $id): ?Maintenances;
}