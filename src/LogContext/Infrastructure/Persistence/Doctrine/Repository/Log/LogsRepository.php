<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\Log;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LogContext\Application\Usecase\Query\Log\GetLogListQuery;
use Websymphonie\LogContext\Domain\Repository\Log\LogModelRepository;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<Logs>
 */
class LogsRepository extends ServiceEntityRepository implements LogModelRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager)
    {
        parent::__construct($registry, Logs::class);
    }

    public function create(Logs $entity): Logs
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function update(Logs $entity): Logs
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function remove(Logs $entity): void
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
     * @return Logs|null
     */
    public function getById(int $id): ?Logs
    {
        return $this->createQueryBuilder('l')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return Query<mixed, mixed> */
    public function getLogQuery(GetLogListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('l');
        if ($query->message !== null) {
            $qb = $qb->andWhere('l.message LIKE :message')
                ->setParameter('message', '%' . $query->message . '%');
        }
        if ($query->levelName !== null) {
            $qb = $qb->andWhere('l.levelName LIKE :levelName')
                ->setParameter('levelName', '%' . $query->levelName . '%');
        }

        return $qb->addOrderBy('l.id', 'DESC')->getQuery();
    }
}
