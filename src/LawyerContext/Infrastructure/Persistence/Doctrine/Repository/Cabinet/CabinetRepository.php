<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;

/** @extends ServiceEntityRepository<CabinetEntity> */
final class CabinetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CabinetEntity::class);
    }

    /** @return list<CabinetEntity> */
    public function listOrdered(): array
    {
        return $this->createQueryBuilder('cabinet')
            ->orderBy('cabinet.name', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return list<CabinetEntity> */
    public function listActive(): array
    {
        return $this->createQueryBuilder('cabinet')
            ->andWhere('cabinet.status = :status')->setParameter('status', 'ACTIVE')
            ->orderBy('cabinet.name', 'ASC')
            ->getQuery()->getResult();
    }
}
