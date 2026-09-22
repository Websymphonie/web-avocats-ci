<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\EditorialVideoCategory;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoCategoryNotFoundException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategoryListResult;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoCategoryRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideoCategory\EditorialVideoCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\EditorialVideoCategoryFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<EditorialVideoCategoryEntity> */
final class EditorialVideoCategoryRepository extends ServiceEntityRepository implements EditorialVideoCategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly EditorialVideoCategoryFactory $factory)
    {
        parent::__construct($registry, EditorialVideoCategoryEntity::class);
    }

    public function save(EditorialVideoCategory $category): EditorialVideoCategory
    {
        $entity = $category->id > 0 ? $this->find($category->id) : null;
        $entity = $this->factory->toEntity($category, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $category->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); }
        finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): EditorialVideoCategory
    {
        $entity = $this->find($id);
        if (!$entity instanceof EditorialVideoCategoryEntity) { throw EditorialVideoCategoryNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    /** @return list<EditorialVideoCategory> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) { return []; }
        return array_map(fn (EditorialVideoCategoryEntity $entity): EditorialVideoCategory => $this->factory->fromEntity($entity), $this->createQueryBuilder('category')->andWhere('category.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult());
    }

    public function list(?string $search, int $page, int $limit): EditorialVideoCategoryListResult
    {
        $qb = $this->createQueryBuilder('category');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(category.name) LIKE LOWER(:search) OR LOWER(category.slug) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        $total = (int) (clone $qb)->select('COUNT(category.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('category.name', 'ASC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new EditorialVideoCategoryListResult(array_map(fn (EditorialVideoCategoryEntity $entity): EditorialVideoCategory => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $qb = $this->createQueryBuilder('category')->select('COUNT(category.id)')->where('category.slug = :slug')->setParameter('slug', $slug);
        if ($exceptId !== null) { $qb->andWhere('category.id != :exceptId')->setParameter('exceptId', $exceptId); }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countVideoUsage(int $id): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()->select('COUNT(video.id)')->from(EditorialVideoEntity::class, 'video')->join('video.category', 'category')->where('category.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
    }

    public function delete(EditorialVideoCategory $category): void
    {
        $entity = $this->find($category->id);
        if (!$entity instanceof EditorialVideoCategoryEntity) { throw EditorialVideoCategoryNotFoundException::withId($category->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); }
        finally { DbLogListener::enable(); }
    }
}
