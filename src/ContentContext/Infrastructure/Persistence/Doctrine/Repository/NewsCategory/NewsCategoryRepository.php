<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\NewsCategory;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Exception\NewsCategoryNotFoundException;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\NewsCategoryListResult;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\NewsCategoryFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<NewsCategoryEntity> */
final class NewsCategoryRepository extends ServiceEntityRepository implements NewsCategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly NewsCategoryFactory $factory)
    {
        parent::__construct($registry, NewsCategoryEntity::class);
    }

    public function save(NewsCategory $category): NewsCategory
    {
        $entity = $category->id > 0 ? $this->find($category->id) : null;
        $entity = $this->factory->toEntity($category, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $category->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); }
        finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): NewsCategory
    {
        $entity = $this->find($id);
        if (!$entity instanceof NewsCategoryEntity) { throw NewsCategoryNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    /** @param list<int> $ids @return list<NewsCategory> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) { return []; }
        return array_map(fn (NewsCategoryEntity $entity): NewsCategory => $this->factory->fromEntity($entity), $this->createQueryBuilder('category')->andWhere('category.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult());
    }

    public function list(?string $search, int $page, int $limit): NewsCategoryListResult
    {
        $qb = $this->createQueryBuilder('category');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(category.name) LIKE LOWER(:search) OR LOWER(category.slug) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        $total = (int) (clone $qb)->select('COUNT(category.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('category.name', 'ASC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new NewsCategoryListResult(array_map(fn (NewsCategoryEntity $entity): NewsCategory => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $qb = $this->createQueryBuilder('category')->select('COUNT(category.id)')->where('category.slug = :slug')->setParameter('slug', $slug);
        if ($exceptId !== null) { $qb->andWhere('category.id != :exceptId')->setParameter('exceptId', $exceptId); }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countNewsUsage(int $id): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()->select('COUNT(news.id)')->from(NewsEntity::class, 'news')->join('news.categories', 'category')->where('category.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
    }

    public function delete(NewsCategory $category): void
    {
        $entity = $this->find($category->id);
        if (!$entity instanceof NewsCategoryEntity) { throw NewsCategoryNotFoundException::withId($category->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); }
        finally { DbLogListener::enable(); }
    }
}
