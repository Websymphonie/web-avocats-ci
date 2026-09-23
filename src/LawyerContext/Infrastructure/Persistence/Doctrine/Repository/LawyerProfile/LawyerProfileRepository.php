<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryEntry;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Domain\Repository\LawyerDirectoryRepositoryInterface;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;

/** @extends ServiceEntityRepository<LawyerProfileEntity> */
final class LawyerProfileRepository extends ServiceEntityRepository implements LawyerDirectoryRepositoryInterface
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

    public function listPublic(string $name, string $cabinet, string $location, int $page, int $limit): LawyerDirectoryResult
    {
        $baseQuery = $this->createQueryBuilder('profile')
            ->innerJoin('profile.user', 'directoryUser')
            ->leftJoin('profile.cabinet', 'directoryCabinet')
            ->where('profile.directoryVisible = :visible')
            ->andWhere('directoryUser.enabled = :enabled')
            ->andWhere('profile.professionalStatus <> :suspended')
            ->setParameter('visible', true)
            ->setParameter('enabled', true)
            ->setParameter('suspended', 'SUSPENDED');

        if ($this->getEntityManager()->getConnection()->getDatabasePlatform() instanceof SQLitePlatform) {
            // SQLite is used by isolated functional/browser checks; match the exact JSON string token.
            $baseQuery->andWhere("directoryUser.roles LIKE :lawyerRole ESCAPE '!'")
                ->setParameter('lawyerRole', '%"ROLE!_AVOCAT"%');
        } else {
            $baseQuery->andWhere('JSON_CONTAINS(directoryUser.roles, :lawyerRole) = 1')
                ->setParameter('lawyerRole', json_encode('ROLE_AVOCAT', JSON_THROW_ON_ERROR));
        }

        if ($name !== '') {
            $baseQuery->andWhere('LOWER(directoryUser.name) LIKE LOWER(:name)')
                ->setParameter('name', '%' . $name . '%');
        }
        if ($cabinet !== '') {
            $baseQuery->andWhere('LOWER(directoryCabinet.name) LIKE LOWER(:cabinet)')
                ->setParameter('cabinet', '%' . $cabinet . '%');
        }
        if ($location !== '') {
            $baseQuery->andWhere('LOWER(directoryCabinet.city) LIKE LOWER(:location)')
                ->setParameter('location', '%' . $location . '%');
        }

        $totalItemCount = (int) (clone $baseQuery)
            ->select('COUNT(profile.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /** @var list<array{publicUuid: Uuid|string, name: string|null, cabinetName: string|null, location: string|null, portraitMediaId: int|string|null}> $rows */
        $rows = (clone $baseQuery)
            ->select('profile.uuid AS publicUuid, directoryUser.name AS name, directoryCabinet.name AS cabinetName, directoryCabinet.city AS location, profile.portraitMediaId AS portraitMediaId')
            ->orderBy('directoryUser.name', 'ASC')
            ->addOrderBy('profile.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $items = array_map(static fn (array $row): LawyerDirectoryEntry => new LawyerDirectoryEntry(
            publicUuid: $row['publicUuid'] instanceof Uuid ? $row['publicUuid']->toRfc4122() : (string) $row['publicUuid'],
            name: (string) $row['name'],
            cabinetName: $row['cabinetName'],
            location: $row['location'],
            portraitMediaId: $row['portraitMediaId'] !== null ? (int) $row['portraitMediaId'] : null,
        ), $rows);

        return new LawyerDirectoryResult($items, $totalItemCount, $page, $limit);
    }
}
