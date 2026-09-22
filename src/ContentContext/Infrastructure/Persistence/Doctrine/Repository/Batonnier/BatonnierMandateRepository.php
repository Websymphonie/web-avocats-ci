<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Batonnier;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Exception\BatonnierMandateNotFoundException;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\BatonnierMandateFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<BatonnierMandateEntity> */
final class BatonnierMandateRepository extends ServiceEntityRepository implements BatonnierMandateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly BatonnierMandateFactory $factory)
    {
        parent::__construct($registry, BatonnierMandateEntity::class);
    }

    public function save(BatonnierMandate $mandate): BatonnierMandate
    {
        $entity = $mandate->id > 0 ? $this->find($mandate->id) : null;
        $entity = $this->factory->toEntity($mandate, $entity instanceof BatonnierMandateEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $mandate->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }

        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): BatonnierMandate
    {
        $entity = $this->find($id);
        if (!$entity instanceof BatonnierMandateEntity) {
            throw BatonnierMandateNotFoundException::withId($id);
        }

        return $this->factory->fromEntity($entity);
    }

    public function findCurrent(): ?BatonnierMandate
    {
        $entity = $this->createQueryBuilder('mandate')
            ->andWhere('mandate.mandateEndedAt IS NULL')
            ->orderBy('mandate.mandateStartedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $entity instanceof BatonnierMandateEntity ? $this->factory->fromEntity($entity) : null;
    }

    /** @return list<BatonnierMandate> */
    public function list(): array
    {
        return array_map(
            fn (BatonnierMandateEntity $entity): BatonnierMandate => $this->factory->fromEntity($entity),
            $this->createQueryBuilder('mandate')
                ->orderBy('mandate.mandateStartedAt', 'DESC')
                ->addOrderBy('mandate.id', 'DESC')
                ->getQuery()->getResult(),
        );
    }

    public function countMediaUsage(int $mediaId): int
    {
        return (int) $this->createQueryBuilder('mandate')
            ->select('COUNT(mandate.id)')
            ->where('mandate.portraitMediaId = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->getQuery()->getSingleScalarResult();
    }
}
