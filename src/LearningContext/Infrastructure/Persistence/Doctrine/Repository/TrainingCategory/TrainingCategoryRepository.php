<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\TrainingCategory;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LearningContext\Domain\Exception\TrainingCategoryNotFoundException;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Model\TrainingCategoryListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\TrainingCategoryFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<TrainingCategoryEntity> */
final class TrainingCategoryRepository extends ServiceEntityRepository implements TrainingCategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly TrainingCategoryFactory $factory) { parent::__construct($registry, TrainingCategoryEntity::class); }
    public function save(TrainingCategory $category): TrainingCategory { $entity = $category->id > 0 ? $this->find($category->id) : null; $entity = $this->factory->toEntity($category, $entity instanceof TrainingCategoryEntity ? $entity : null); DbLogListener::disable(); try { $this->manager->execute($entity, $category->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); } return $this->factory->fromEntity($entity); }
    public function getById(int $id): TrainingCategory { $entity = $this->find($id); if (!$entity instanceof TrainingCategoryEntity) { throw TrainingCategoryNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }
    /** @return list<TrainingCategory> */
    public function findByIds(array $ids): array { if ($ids === []) { return []; } return array_map(fn (TrainingCategoryEntity $entity): TrainingCategory => $this->factory->fromEntity($entity), $this->createQueryBuilder('category')->andWhere('category.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()); }
    public function list(?string $search, int $page, int $limit): TrainingCategoryListResult { $qb = $this->createQueryBuilder('category'); if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(category.name) LIKE LOWER(:search) OR LOWER(category.slug) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); } $total = (int) (clone $qb)->select('COUNT(category.id)')->getQuery()->getSingleScalarResult(); $entities = $qb->orderBy('category.name', 'ASC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult(); return new TrainingCategoryListResult(array_map(fn (TrainingCategoryEntity $entity): TrainingCategory => $this->factory->fromEntity($entity), $entities), $total, $page, $limit); }
    public function slugExists(string $slug, ?int $exceptId = null): bool { $qb = $this->createQueryBuilder('category')->select('COUNT(category.id)')->where('category.slug = :slug')->setParameter('slug', $slug); if ($exceptId !== null) { $qb->andWhere('category.id != :exceptId')->setParameter('exceptId', $exceptId); } return (int) $qb->getQuery()->getSingleScalarResult() > 0; }
    public function countTrainingUsage(int $id): int { return (int) $this->getEntityManager()->createQueryBuilder()->select('COUNT(training.id)')->from(TrainingEntity::class, 'training')->join('training.categories', 'category')->where('category.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult(); }
    public function delete(TrainingCategory $category): void { $entity = $this->find($category->id); if (!$entity instanceof TrainingCategoryEntity) { throw TrainingCategoryNotFoundException::withId($category->id); } DbLogListener::disable(); try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); } }
}
