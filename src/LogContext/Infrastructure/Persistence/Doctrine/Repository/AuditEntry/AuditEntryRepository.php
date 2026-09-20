<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuditEntry;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntryPage;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository as AuditEntryRepositoryInterface;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntrySearchCriteria;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\LogContext\Infrastructure\Persistence\Factory\AuditEntry\AuditEntryFactory;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<\Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuditEntry\AuditEntry> */
final class AuditEntryRepository extends ServiceEntityRepository implements AuditEntryRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ManagersInterface $manager,
        private readonly AuditEntryFactory $factory,
    ) {
        parent::__construct($registry, \Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuditEntry\AuditEntry::class);
    }

    public function record(AuditEntry $entry): AuditEntry
    {
        $entity = $this->factory->toEntity($entry);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }

        return $this->factory->fromEntity($entity);
    }

    public function findById(int $id): ?AuditEntry
    {
        return $this->factory->fromEntity($this->find($id));
    }

    public function findByDeduplicationKey(string $deduplicationKey): ?AuditEntry
    {
        $entity = $this->findOneBy(['deduplicationKey' => $deduplicationKey]);

        return $this->factory->fromEntity($entity);
    }

    public function findPage(AuditEntrySearchCriteria $criteria): AuditEntryPage
    {
        $queryBuilder = $this->createQueryBuilder('audit');
        $this->applyCriteria($queryBuilder, $criteria, 'audit');
        $queryBuilder
            ->orderBy('audit.occurredAt', 'DESC')
            ->addOrderBy('audit.id', 'DESC')
            ->setFirstResult(max(0, $criteria->page - 1) * $criteria->limit)
            ->setMaxResults($criteria->limit);

        $countQueryBuilder = $this->createQueryBuilder('auditCount')
            ->select('COUNT(auditCount.id)');
        $this->applyCriteria($countQueryBuilder, $criteria, 'auditCount');

        $entities = $queryBuilder->getQuery()->getResult();
        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        return new AuditEntryPage(
            items: $this->factory->fromEntities($entities),
            total: $total,
            page: $criteria->page,
            limit: $criteria->limit,
        );
    }

    private function applyCriteria(QueryBuilder $queryBuilder, AuditEntrySearchCriteria $criteria, string $alias): void
    {
        $queryBuilder
            ->andWhere(sprintf('%s.occurredAt >= :from OR :from IS NULL', $alias))
            ->andWhere(sprintf('%s.occurredAt <= :to OR :to IS NULL', $alias))
            ->setParameter('from', $criteria->from, Types::DATETIME_IMMUTABLE)
            ->setParameter('to', $criteria->to, Types::DATETIME_IMMUTABLE);

        foreach ([
            'context' => $criteria->context,
            'action' => $criteria->action,
            'actorId' => $criteria->actorId,
            'targetType' => $criteria->targetType,
            'targetId' => $criteria->targetId,
        ] as $field => $value) {
            if ($value !== null && $value !== '') {
                $queryBuilder->andWhere(sprintf('%s.%s = :%s', $alias, $field, $field))->setParameter($field, $value);
            }
        }
    }
}
