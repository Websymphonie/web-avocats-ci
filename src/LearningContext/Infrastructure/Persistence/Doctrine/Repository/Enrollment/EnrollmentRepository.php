<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Enrollment;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Exception\EnrollmentNotFoundException;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListResult;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\EnrollmentFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<EnrollmentEntity> */
final class EnrollmentRepository extends ServiceEntityRepository implements EnrollmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly EnrollmentFactory $factory)
    {
        parent::__construct($registry, EnrollmentEntity::class);
    }

    public function save(Enrollment $enrollment): Enrollment
    {
        $entity = $enrollment->id > 0 ? $this->find($enrollment->id) : null;
        $entity = $this->factory->toEntity($enrollment, $entity instanceof EnrollmentEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $enrollment->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): Enrollment
    {
        $entity = $this->find($id);
        if (!$entity instanceof EnrollmentEntity) { throw EnrollmentNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    public function getByUuid(string $uuid): Enrollment
    {
        $entity = $this->createQueryBuilder('enrollment')->andWhere('enrollment.uuid = :uuid')->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)->getQuery()->getOneOrNullResult();
        if (!$entity instanceof EnrollmentEntity) { throw EnrollmentNotFoundException::withUuid($uuid); }
        return $this->factory->fromEntity($entity);
    }

    public function findByTrainingAndUser(int $trainingId, int $userId): ?Enrollment
    {
        $entity = $this->findOneBy(['trainingId' => $trainingId, 'userId' => $userId]);
        return $entity instanceof EnrollmentEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function countByTraining(int $trainingId): int
    {
        return (int) $this->createQueryBuilder('enrollment')->select('COUNT(enrollment.id)')->where('enrollment.trainingId = :trainingId')->setParameter('trainingId', $trainingId)->getQuery()->getSingleScalarResult();
    }

    public function countActiveByTraining(int $trainingId): int
    {
        return (int) $this->createQueryBuilder('enrollment')->select('COUNT(enrollment.id)')->where('enrollment.trainingId = :trainingId')->andWhere('enrollment.status = :status')->setParameter('trainingId', $trainingId)->setParameter('status', EnrollmentStatus::ACTIVE)->getQuery()->getSingleScalarResult();
    }

    /** @return list<Enrollment> */
    public function listByUser(int $userId, ?EnrollmentStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('enrollment')
            ->where('enrollment.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('enrollment.createdAt', 'DESC');
        if ($status !== null) {
            $qb->andWhere('enrollment.status = :status')->setParameter('status', $status);
        }

        return array_map(
            fn (EnrollmentEntity $entity): Enrollment => $this->factory->fromEntity($entity),
            $qb->getQuery()->getResult(),
        );
    }

    /** @param list<int>|null $userIds */
    public function listByTraining(int $trainingId, ?EnrollmentStatus $status, ?EnrollmentSource $source, ?array $userIds, int $page, int $limit): EnrollmentListResult
    {
        $qb = $this->createQueryBuilder('enrollment')->where('enrollment.trainingId = :trainingId')->setParameter('trainingId', $trainingId);
        if ($status !== null) { $qb->andWhere('enrollment.status = :status')->setParameter('status', $status); }
        if ($source !== null) { $qb->andWhere('enrollment.source = :source')->setParameter('source', $source); }
        if ($userIds !== null) {
            if ($userIds === []) { return new EnrollmentListResult([], 0, $page, $limit); }
            $qb->andWhere('enrollment.userId IN (:userIds)')->setParameter('userIds', $userIds);
        }
        $total = (int) (clone $qb)->select('COUNT(enrollment.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('enrollment.createdAt', 'DESC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new EnrollmentListResult(array_map(fn (EnrollmentEntity $entity): Enrollment => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }
}
