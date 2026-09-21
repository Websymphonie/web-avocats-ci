<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Training;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\TrainingNotFoundException;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\TrainingListResult;
use Websymphonie\LearningContext\Domain\Model\TrainingSummary;
use Websymphonie\LearningContext\Domain\Repository\LiveTrainingDetailsRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\TrainingFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<TrainingEntity> */
final class TrainingRepository extends ServiceEntityRepository implements TrainingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ManagersInterface $manager,
        private readonly TrainingFactory $factory,
        private readonly LiveTrainingDetailsRepositoryInterface $liveDetailsRepository,
    ) {
        parent::__construct($registry, TrainingEntity::class);
    }

    public function save(Training $training): Training
    {
        $entity = $training->id > 0 ? $this->find($training->id) : null;
        $categoryEntities = array_map(fn (int $id): TrainingCategoryEntity => $this->getEntityManager()->getReference(TrainingCategoryEntity::class, $id), $training->categoryIds);
        $tagEntities = array_map(fn (int $id): TrainingTagEntity => $this->getEntityManager()->getReference(TrainingTagEntity::class, $id), $training->tagIds);
        $entity = $this->factory->toEntity($training, $entity, $categoryEntities, $tagEntities);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, $training->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }

        if ($training->type === TrainingType::LIVE && $training->liveDetails !== null) {
            $training->liveDetails->attachToTraining($entity->getId() ?? 0);
            $training->liveDetails = $this->liveDetailsRepository->save($training->liveDetails);
        }

        return $this->factory->fromEntity($entity, $training->liveDetails);
    }

    public function getById(int $id): Training
    {
        $entity = $this->find($id);
        if (!$entity instanceof TrainingEntity) {
            throw TrainingNotFoundException::withId($id);
        }

        return $this->withLiveDetails($entity);
    }

    public function getByUuid(string $uuid): Training
    {
        $entity = $this->createQueryBuilder('training')->andWhere('training.uuid = :uuid')->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)->getQuery()->getOneOrNullResult();
        if (!$entity instanceof TrainingEntity) { throw TrainingNotFoundException::withUuid($uuid); }
        return $this->withLiveDetails($entity);
    }

    public function getPublicBySlug(string $slug): Training
    {
        $entity = $this->createQueryBuilder('training')
            ->leftJoin('training.categories', 'category')->addSelect('category')
            ->leftJoin('training.tags', 'tag')->addSelect('tag')
            ->where('training.slug = :slug')
            ->andWhere('training.status = :status')
            ->andWhere('training.visibility = :visibility')
            ->andWhere('training.publishedAt IS NOT NULL')
            ->setParameter('slug', $slug)
            ->setParameter('status', TrainingStatus::PUBLISHED)
            ->setParameter('visibility', TrainingVisibility::PUBLIC)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity instanceof TrainingEntity) {
            throw TrainingNotFoundException::withSlug($slug);
        }

        return $this->withLiveDetails($entity);
    }

    public function delete(Training $training): void
    {
        $entity = $this->find($training->id);
        if (!$entity instanceof TrainingEntity) {
            throw TrainingNotFoundException::withId($training->id);
        }

        if ($training->type === TrainingType::LIVE) {
            $this->liveDetailsRepository->deleteByTrainingId($training->id);
        }

        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $qb = $this->createQueryBuilder('training')
            ->select('COUNT(training.id)')
            ->where('training.slug = :slug')
            ->setParameter('slug', $slug);
        if ($exceptId !== null) {
            $qb->andWhere('training.id != :exceptId')->setParameter('exceptId', $exceptId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countMediaUsage(int $mediaId): int
    {
        return (int) $this->createQueryBuilder('training')
            ->select('COUNT(training.id)')
            ->where('training.coverMediaId = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<Training> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $entities = $this->createQueryBuilder('training')
                ->andWhere('training.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();
        $models = [];
        $liveIds = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof TrainingEntity) {
                continue;
            }
            $model = $this->factory->fromEntity($entity);
            $models[$model->id] = $model;
            if ($model->type === TrainingType::LIVE) {
                $liveIds[] = $model->id;
            }
        }
        $liveDetails = $this->liveDetailsRepository->findByTrainingIds($liveIds);
        foreach ($models as $model) {
            if ($model->type === TrainingType::LIVE) {
                $model->replaceLiveDetails($liveDetails[$model->id] ?? null);
            }
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($models[$id])) {
                $ordered[] = $models[$id];
            }
        }
        return $ordered;
    }

    /** @return list<TrainingSummary> */
    public function findSummariesByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('training')
            ->select('training.id AS id, training.uuid AS uuid, training.title AS title, training.type AS type, training.status AS status, training.accessType AS accessType')
            ->andWhere('training.id IN (:ids)')
            ->setParameter('ids', array_values(array_unique($ids)))
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): TrainingSummary => new TrainingSummary(
                id: (int) $row['id'],
                uuid: (string) $row['uuid'],
                title: (string) $row['title'],
                type: self::scalarValue($row['type']),
                status: self::scalarValue($row['status']),
                accessType: self::scalarValue($row['accessType']),
            ),
            $rows,
        );
    }

    public function list(
        ?string $search,
        ?TrainingStatus $status,
        ?TrainingVisibility $visibility,
        ?TrainingAccessType $accessType,
        ?TrainingType $type,
        ?int $categoryId,
        ?int $tagId,
        int $page,
        int $limit,
    ): TrainingListResult {
        $qb = $this->createQueryBuilder('training');
        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(training.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%');
        }
        if ($status !== null) {
            $qb->andWhere('training.status = :status')->setParameter('status', $status);
        }
        if ($visibility !== null) {
            $qb->andWhere('training.visibility = :visibility')->setParameter('visibility', $visibility);
        }
        if ($accessType !== null) {
            $qb->andWhere('training.accessType = :accessType')->setParameter('accessType', $accessType);
        }
        if ($type !== null) {
            $qb->andWhere('training.type = :type')->setParameter('type', $type);
        }
        if ($categoryId !== null) { $qb->join('training.categories', 'category')->andWhere('category.id = :categoryId')->setParameter('categoryId', $categoryId); }
        if ($tagId !== null) { $qb->join('training.tags', 'tag')->andWhere('tag.id = :tagId')->setParameter('tagId', $tagId); }

        $total = (int) (clone $qb)->select('COUNT(training.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('training.updatedAt', 'DESC')
            ->addOrderBy('training.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $models = array_map(fn (TrainingEntity $entity): Training => $this->factory->fromEntity($entity), $entities);
        $liveIds = array_values(array_map(
            static fn (Training $training): int => $training->id,
            array_filter($models, static fn (Training $training): bool => $training->type === TrainingType::LIVE),
        ));
        $liveDetails = $this->liveDetailsRepository->findByTrainingIds($liveIds);
        foreach ($models as $model) {
            if ($model->type === TrainingType::LIVE) {
                $model->replaceLiveDetails($liveDetails[$model->id] ?? null);
            }
        }

        return new TrainingListResult($models, $total, $page, $limit);
    }

    /** @return list<Training> */
    public function searchPublic(string $term, int $limit): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $entities = $this->createQueryBuilder('training')
            ->leftJoin('training.categories', 'category')->addSelect('category')
            ->leftJoin('training.tags', 'tag')->addSelect('tag')
            ->distinct()
            ->where('training.status = :status')
            ->andWhere('training.visibility = :visibility')
            ->andWhere('training.publishedAt IS NOT NULL')
            ->andWhere('(LOWER(training.title) LIKE LOWER(:term) OR LOWER(training.summary) LIKE LOWER(:term) OR LOWER(category.name) LIKE LOWER(:term))')
            ->setParameter('status', TrainingStatus::PUBLISHED)
            ->setParameter('visibility', TrainingVisibility::PUBLIC)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('training.publishedAt', 'DESC')
            ->addOrderBy('training.id', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();

        $models = array_map(fn (TrainingEntity $entity): Training => $this->factory->fromEntity($entity), $entities);
        $liveIds = array_values(array_map(
            static fn (Training $training): int => $training->id,
            array_filter($models, static fn (Training $training): bool => $training->type === TrainingType::LIVE),
        ));
        $liveDetails = $this->liveDetailsRepository->findByTrainingIds($liveIds);
        foreach ($models as $model) {
            if ($model->type === TrainingType::LIVE) {
                $model->replaceLiveDetails($liveDetails[$model->id] ?? null);
            }
        }

        return $models;
    }

    private function withLiveDetails(TrainingEntity $entity): Training
    {
        $model = $this->factory->fromEntity($entity);
        if ($model->type === TrainingType::LIVE) {
            $model->replaceLiveDetails($this->liveDetailsRepository->findByTrainingId($model->id));
        }
        return $model;
    }

    private static function scalarValue(mixed $value): string
    {
        return $value instanceof \BackedEnum ? $value->value : (string) $value;
    }
}
