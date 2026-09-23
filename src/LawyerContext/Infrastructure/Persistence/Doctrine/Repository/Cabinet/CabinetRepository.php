<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Domain\Model\CabinetPublicProfile;
use Websymphonie\LawyerContext\Domain\Repository\PublicCabinetDirectoryRepositoryInterface;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;

/** @extends ServiceEntityRepository<CabinetEntity> */
final class CabinetRepository extends ServiceEntityRepository implements PublicCabinetDirectoryRepositoryInterface
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

    public function findPublicByUuid(string $uuid): ?CabinetPublicProfile
    {
        if (!Uuid::isValid($uuid)) {
            return null;
        }

        /** @var array{publicUuid: Uuid|string, name: string, address: string|null, city: string|null, country: string, phone: string|null, email: string|null, websiteUrl: string|null, description: string|null}|null $row */
        $row = $this->createQueryBuilder('cabinet')
            ->select('cabinet.uuid AS publicUuid, cabinet.name AS name, cabinet.address AS address, cabinet.city AS city, cabinet.country AS country, cabinet.phone AS phone, cabinet.email AS email, cabinet.websiteUrl AS websiteUrl, cabinet.description AS description')
            ->where('cabinet.uuid = :uuid')
            ->andWhere('cabinet.status = :status')
            ->andWhere('cabinet.directoryVisible = :visible')
            ->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)
            ->setParameter('status', 'ACTIVE')
            ->setParameter('visible', true)
            ->getQuery()
            ->getOneOrNullResult();

        if ($row === null) {
            return null;
        }

        return new CabinetPublicProfile(
            publicUuid: $row['publicUuid'] instanceof Uuid ? $row['publicUuid']->toRfc4122() : (string) $row['publicUuid'],
            name: $row['name'],
            address: $row['address'],
            city: $row['city'],
            country: $row['country'],
            phone: $row['phone'],
            email: $row['email'],
            websiteUrl: $row['websiteUrl'],
            description: $row['description'],
        );
    }
}
