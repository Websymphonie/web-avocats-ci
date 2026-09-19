<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Factory;

use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance\Maintenances;

final class MaintenanceFactory
{
    /**
     * @param list<Maintenances> $entities
     * @return list<MaintenanceModel>
     */
    public static function fromEntityList(array $entities): array
    {
        return array_map(fn(Maintenances $entity) => self::fromEntity($entity), $entities);
    }

    /**
     * @param Maintenances|null $entity
     * @return MaintenanceModel|null
     */
    public static function fromEntity(?Maintenances $entity): ?MaintenanceModel
    {
        if ($entity === null) {
            return null;
        }
        return new MaintenanceModel(
            id: $entity->getId(),
            uuid: $entity->getUuidAsString(),
            active: $entity->getActive(),
        );
    }
}
