<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\TrainingTag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LearningContext\Domain\Exception\TrainingTagNotFoundException;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\TrainingTagFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<TrainingTagEntity> */
final class TrainingTagRepository extends ServiceEntityRepository implements TrainingTagRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly TrainingTagFactory $factory) { parent::__construct($registry, TrainingTagEntity::class); }
    public function save(TrainingTag $tag): TrainingTag { $entity = $tag->id > 0 ? $this->find($tag->id) : null; $entity = $this->factory->toEntity($tag, $entity instanceof TrainingTagEntity ? $entity : null); DbLogListener::disable(); try { $this->manager->execute($entity, $tag->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); } return $this->factory->fromEntity($entity); }
    public function getById(int $id): TrainingTag { $entity = $this->find($id); if (!$entity instanceof TrainingTagEntity) { throw TrainingTagNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }
    /** @return list<TrainingTag> */
    public function findByIds(array $ids): array { if ($ids === []) { return []; } return array_map(fn (TrainingTagEntity $entity): TrainingTag => $this->factory->fromEntity($entity), $this->createQueryBuilder('tag')->andWhere('tag.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()); }
    public function list(?string $search, int $page, int $limit): TrainingTagListResult { $qb = $this->createQueryBuilder('tag'); if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(tag.name) LIKE LOWER(:search) OR LOWER(tag.slug) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); } $total = (int) (clone $qb)->select('COUNT(tag.id)')->getQuery()->getSingleScalarResult(); $entities = $qb->orderBy('tag.name', 'ASC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult(); return new TrainingTagListResult(array_map(fn (TrainingTagEntity $entity): TrainingTag => $this->factory->fromEntity($entity), $entities), $total, $page, $limit); }
    public function slugExists(string $slug, ?int $exceptId = null): bool { $qb = $this->createQueryBuilder('tag')->select('COUNT(tag.id)')->where('tag.slug = :slug')->setParameter('slug', $slug); if ($exceptId !== null) { $qb->andWhere('tag.id != :exceptId')->setParameter('exceptId', $exceptId); } return (int) $qb->getQuery()->getSingleScalarResult() > 0; }
    public function countTrainingUsage(int $id): int { return (int) $this->getEntityManager()->createQueryBuilder()->select('COUNT(training.id)')->from(TrainingEntity::class, 'training')->join('training.tags', 'tag')->where('tag.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult(); }
    public function delete(TrainingTag $tag): void { $entity = $this->find($tag->id); if (!$entity instanceof TrainingTagEntity) { throw TrainingTagNotFoundException::withId($tag->id); } DbLogListener::disable(); try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); } }
}
