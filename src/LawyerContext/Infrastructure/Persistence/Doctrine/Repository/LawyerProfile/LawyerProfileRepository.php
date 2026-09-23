<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryEntry;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryMember;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Domain\Model\LawyerPublicProfile;
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
        $baseQuery = $this->createPublicLawyerQueryBuilder();

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
            ->select("profile.uuid AS publicUuid, directoryUser.name AS name, directoryCabinet.name AS cabinetName, CASE WHEN directoryCabinet.status = 'ACTIVE' AND directoryCabinet.directoryVisible = true THEN directoryCabinet.city ELSE '' END AS location, profile.portraitMediaId AS portraitMediaId")
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
            location: $row['location'] !== '' ? $row['location'] : null,
            portraitMediaId: $row['portraitMediaId'] !== null ? (int) $row['portraitMediaId'] : null,
        ), $rows);

        return new LawyerDirectoryResult($items, $totalItemCount, $page, $limit);
    }

    public function findPublicProfileByUuid(string $uuid): ?LawyerPublicProfile
    {
        if (!Uuid::isValid($uuid)) {
            return null;
        }

        /** @var array{publicUuid: Uuid|string, name: string|null, barNumber: string|null, specializationSummary: string|null, biography: string|null, professionalPhone: string|null, professionalEmail: string|null, portraitMediaId: int|string|null, cabinetName: string|null, cabinetUuid: Uuid|string|null, cabinetCity: string|null, cabinetStatus: string|null, cabinetVisible: bool|int|null}|null $row */
        $row = $this->createPublicLawyerQueryBuilder()
            ->andWhere('profile.uuid = :uuid')
            ->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)
            ->select('profile.uuid AS publicUuid, directoryUser.name AS name, profile.barNumber AS barNumber, profile.specializationSummary AS specializationSummary, profile.bio AS biography, profile.professionalPhone AS professionalPhone, profile.professionalEmail AS professionalEmail, profile.portraitMediaId AS portraitMediaId, directoryCabinet.name AS cabinetName, directoryCabinet.uuid AS cabinetUuid, directoryCabinet.city AS cabinetCity, directoryCabinet.status AS cabinetStatus, directoryCabinet.directoryVisible AS cabinetVisible')
            ->getQuery()
            ->getOneOrNullResult();

        if ($row === null) {
            return null;
        }

        $isPublicCabinet = $row['cabinetUuid'] !== null && $row['cabinetStatus'] === 'ACTIVE' && (bool) $row['cabinetVisible'];

        return new LawyerPublicProfile(
            publicUuid: $this->uuidString($row['publicUuid']),
            name: (string) $row['name'],
            barNumber: $row['barNumber'],
            specializationSummary: $row['specializationSummary'],
            biography: $row['biography'],
            professionalPhone: $row['professionalPhone'],
            professionalEmail: $row['professionalEmail'],
            portraitMediaId: $row['portraitMediaId'] !== null ? (int) $row['portraitMediaId'] : null,
            cabinetName: $row['cabinetName'],
            publicCabinetUuid: $isPublicCabinet ? $this->uuidString($row['cabinetUuid']) : null,
            publicCabinetCity: $isPublicCabinet ? $row['cabinetCity'] : null,
        );
    }

    public function listPublicMembersByCabinetUuid(string $cabinetUuid): array
    {
        if (!Uuid::isValid($cabinetUuid)) {
            return [];
        }

        /** @var list<array{publicUuid: Uuid|string, name: string|null, portraitMediaId: int|string|null}> $rows */
        $rows = $this->createPublicLawyerQueryBuilder()
            ->andWhere('directoryCabinet.uuid = :cabinetUuid')
            ->andWhere('directoryCabinet.status = :activeCabinet')
            ->andWhere('directoryCabinet.directoryVisible = :publicCabinet')
            ->setParameter('cabinetUuid', Uuid::fromString($cabinetUuid), UuidType::NAME)
            ->setParameter('activeCabinet', 'ACTIVE')
            ->setParameter('publicCabinet', true)
            ->select('profile.uuid AS publicUuid, directoryUser.name AS name, profile.portraitMediaId AS portraitMediaId')
            ->orderBy('directoryUser.name', 'ASC')
            ->addOrderBy('profile.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(fn (array $row): LawyerDirectoryMember => new LawyerDirectoryMember(
            publicUuid: $this->uuidString($row['publicUuid']),
            name: (string) $row['name'],
            portraitMediaId: $row['portraitMediaId'] !== null ? (int) $row['portraitMediaId'] : null,
        ), $rows);
    }

    private function createPublicLawyerQueryBuilder(): QueryBuilder
    {
        $query = $this->createQueryBuilder('profile')
            ->innerJoin('profile.user', 'directoryUser')
            ->leftJoin('profile.cabinet', 'directoryCabinet')
            ->where('profile.directoryVisible = :visible')
            ->andWhere('directoryUser.enabled = :enabled')
            ->andWhere('profile.professionalStatus <> :suspended')
            ->setParameter('visible', true)
            ->setParameter('enabled', true)
            ->setParameter('suspended', 'SUSPENDED');

        if ($this->getEntityManager()->getConnection()->getDatabasePlatform() instanceof SQLitePlatform) {
            $query->andWhere("directoryUser.roles LIKE :lawyerRole ESCAPE '!'")
                ->setParameter('lawyerRole', '%"ROLE!_AVOCAT"%');
        } else {
            $query->andWhere('JSON_CONTAINS(directoryUser.roles, :lawyerRole) = 1')
                ->setParameter('lawyerRole', json_encode('ROLE_AVOCAT', JSON_THROW_ON_ERROR));
        }

        return $query;
    }

    private function uuidString(Uuid|string $uuid): string
    {
        return $uuid instanceof Uuid ? $uuid->toRfc4122() : $uuid;
    }
}
