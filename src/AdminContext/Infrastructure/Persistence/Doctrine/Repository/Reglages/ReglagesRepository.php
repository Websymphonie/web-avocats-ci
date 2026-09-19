<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Repository\Reglages;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\AdminContext\Application\Usecase\Command\Reglage\UpdateReglageCommand;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\GetReglagePaginateListQuery;
use Websymphonie\AdminContext\Application\Usecase\Query\Reglage\ReglageListQuery;
use Websymphonie\AdminContext\Domain\Exception\ReglageNotFound;
use Websymphonie\AdminContext\Domain\Repository\Reglage\ReglageModelRepository;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<Reglages>
 */
class ReglagesRepository extends ServiceEntityRepository implements ReglageModelRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager)
    {
        parent::__construct($registry, Reglages::class);
    }

    /**
     * @param Reglages $entity
     * @return Reglages
     */
    public function create(Reglages $entity): Reglages
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    /**
     * @param Reglages $entity
     * @return Reglages
     */
    public function update(Reglages $entity): Reglages
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    /**
     * @param Reglages $entity
     * @return void
     */
    public function remove(Reglages $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    /**
     * @param int $id
     * @return Reglages
     */
    public function getById(int $id): Reglages
    {
        $reglage = $this->find($id);

        if ($reglage === null) {
            throw ReglageNotFound::withId($id);
        }

        return $reglage;
    }

    /**
     * @param ReglageListQuery $query
     * @return list<Reglages>
     */
    public function findALLForTwig(ReglageListQuery $query): array
    {
        return $this->createQueryBuilder('r', 'r.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param GetReglagePaginateListQuery $query
     * @return Query<mixed, mixed>
     */
    public function getReglageQuery(GetReglagePaginateListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('r');
        if ($query->value !== null) {
            $qb = $qb->andWhere('r.value LIKE :value')
                ->setParameter('value', '%' . $query->value . '%');
        }

        return $qb->orderBy('r.updatedAt', 'DESC')->getQuery();
    }

    /**
     * @param int $id
     * @return UpdateReglageCommand
     */
    public function createCommandFromReglage(int $id): UpdateReglageCommand
    {
        $reglage = $this->find($id);

        if ($reglage === null) {
            throw ReglageNotFound::withId($id);
        }
        return new UpdateReglageCommand(
            id: $reglage->getId(),
            value: $reglage->getValue(),
            label: $reglage->getLabel(),
            type: $reglage->getType(),
        );
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getValue(string $name): ?Reglages
    {
        return $this->createQueryBuilder('r')
            ->where('r.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
