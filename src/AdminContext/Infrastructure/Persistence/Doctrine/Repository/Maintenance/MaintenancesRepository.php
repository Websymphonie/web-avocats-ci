<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Maintenance;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\AdminContext\Domain\Model\Maintenance\MaintenanceModel;
use Websymphonie\AdminContext\Domain\Repository\Maintenance\MaintenanceModelRepository;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance\Maintenances;
use Websymphonie\AdminContext\Infrastructure\Persistence\Factory\MaintenanceFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<Maintenances>
 */
class MaintenancesRepository extends ServiceEntityRepository implements MaintenanceModelRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager)
    {
        parent::__construct($registry, Maintenances::class);
    }

    /**
     * @param Maintenances $entity
     * @return MaintenanceModel
     */
    public function create(Maintenances $entity): MaintenanceModel
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return MaintenanceFactory::fromEntity($entity);
    }

    /**
     * @param Maintenances $entity
     * @return MaintenanceModel
     */
    public function update(Maintenances $entity): MaintenanceModel
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return MaintenanceFactory::fromEntity($entity);
    }

    /**
     * @param Maintenances $entity
     * @return void
     */
    public function remove(Maintenances $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    public function getById(int $id): ?MaintenanceModel
    {
        $maintenance = $this->createQueryBuilder('m')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if ($maintenance === null) {
            return null;
        }

        return MaintenanceFactory::fromEntity($maintenance);
    }

    /**
     * @param int $id
     * @return Maintenances|null
     */
    public function getByEntityId(int $id): ?Maintenances
    {
        return $this->createQueryBuilder('m')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }
}
