<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;

/** @extends ServiceEntityRepository<LawyerProfileEntity> */
final class LawyerProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LawyerProfileEntity::class);
    }

    public function findOneByUser(User $user): ?LawyerProfileEntity
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function countPortraitMediaUsage(int $mediaId): int
    {
        return (int) $this->createQueryBuilder('lawyerProfile')
            ->select('COUNT(lawyerProfile.id)')
            ->where('lawyerProfile.portraitMediaId = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
