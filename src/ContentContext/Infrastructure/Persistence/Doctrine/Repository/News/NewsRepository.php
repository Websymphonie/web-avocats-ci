<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\News;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Exception\NewsNotFoundException;
use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Model\NewsListResult;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\NewsFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<NewsEntity> */
final class NewsRepository extends ServiceEntityRepository implements NewsRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ManagersInterface $manager,
        private readonly NewsFactory $factory,
    ) {
        parent::__construct($registry, NewsEntity::class);
    }

    public function save(News $news): News
    {
        $entity = $news->id > 0 ? $this->find($news->id) : null;
        $categoryEntities = array_values(array_filter(array_map(fn (NewsCategory $category): ?NewsCategoryEntity => $this->getEntityManager()->find(NewsCategoryEntity::class, $category->id), $news->categories)));
        $tagEntities = array_values(array_filter(array_map(fn (Tag $tag): ?TagEntity => $this->getEntityManager()->find(TagEntity::class, $tag->id), $news->tags)));
        $entity = $this->factory->toEntity($news, $entity, $categoryEntities, $tagEntities);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, $news->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }

        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): News
    {
        $entity = $this->find($id);
        if (!$entity instanceof NewsEntity) {
            throw NewsNotFoundException::withId($id);
        }

        return $this->factory->fromEntity($entity);
    }

    public function delete(News $news): void
    {
        $entity = $this->find($news->id);
        if (!$entity instanceof NewsEntity) {
            throw NewsNotFoundException::withId($news->id);
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
        $qb = $this->createQueryBuilder('news')->select('COUNT(news.id)')->where('news.slug = :slug')->setParameter('slug', $slug);
        if ($exceptId !== null) {
            $qb->andWhere('news.id != :exceptId')->setParameter('exceptId', $exceptId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param list<int> $ids
     * @return list<News>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map(
            fn (NewsEntity $entity): News => $this->factory->fromEntity($entity),
            $this->createQueryBuilder('news')->andWhere('news.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()
        );
    }

    public function countMediaUsage(int $mediaId): int { return (int) $this->createQueryBuilder('news')->select('COUNT(news.id)')->where('news.coverMediaId = :mediaId')->setParameter('mediaId', $mediaId)->getQuery()->getSingleScalarResult(); }
    public function countPhotoGalleryUsage(int $galleryId): int { return (int) $this->createQueryBuilder('news')->select('COUNT(news.id)')->where('news.photoGalleryId = :galleryId')->setParameter('galleryId', $galleryId)->getQuery()->getSingleScalarResult(); }

    public function list(?string $search, ?NewsStatus $status, int $page, int $limit, ?int $categoryId = null, ?int $tagId = null): NewsListResult
    {
        $qb = $this->createQueryBuilder('news');
        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('LOWER(news.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%');
        }
        if ($status !== null) {
            $qb->andWhere('news.status = :status')->setParameter('status', $status);
        }
        if ($categoryId !== null) {
            $qb->join('news.categories', 'category_filter')->andWhere('category_filter.id = :categoryId')->setParameter('categoryId', $categoryId);
        }
        if ($tagId !== null) {
            $qb->join('news.tags', 'tag_filter')->andWhere('tag_filter.id = :tagId')->setParameter('tagId', $tagId);
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(news.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('news.updatedAt', 'DESC')->addOrderBy('news.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();

        return new NewsListResult(array_map(fn (NewsEntity $entity): News => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function listPublished(int $page, int $limit, ?int $categoryId = null, ?int $tagId = null): NewsListResult
    {
        $baseQuery = $this->createQueryBuilder('news')
            ->where('news.status = :status')
            ->andWhere('news.publishedAt IS NOT NULL')
            ->setParameter('status', NewsStatus::PUBLISHED);

        $this->applyPublicFilters($baseQuery, $categoryId, $tagId);

        $total = (int) (clone $baseQuery)
            ->select('COUNT(DISTINCT news.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $ids = (clone $baseQuery)
            ->select('news.id')
            ->orderBy('news.publishedAt', 'DESC')
            ->addOrderBy('news.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        if ($ids === []) {
            return new NewsListResult([], $total, $page, $limit);
        }

        $entities = $this->createQueryBuilder('news')
            ->leftJoin('news.categories', 'category')->addSelect('category')
            ->leftJoin('news.tags', 'tag')->addSelect('tag')
            ->where('news.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('news.publishedAt', 'DESC')
            ->addOrderBy('news.id', 'DESC')
            ->getQuery()
            ->getResult();

        return new NewsListResult(array_map(fn (NewsEntity $entity): News => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    /** @return list<News> */
    public function searchPublished(string $term, int $limit): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $entities = $this->createQueryBuilder('news')
            ->leftJoin('news.categories', 'category')->addSelect('category')
            ->leftJoin('news.tags', 'tag')->addSelect('tag')
            ->where('news.status = :status')
            ->andWhere('news.publishedAt IS NOT NULL')
            ->andWhere('(LOWER(news.title) LIKE LOWER(:term) OR LOWER(COALESCE(news.excerpt, \'\')) LIKE LOWER(:term))')
            ->setParameter('status', NewsStatus::PUBLISHED)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('news.publishedAt', 'DESC')
            ->addOrderBy('news.id', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();

        return array_map(fn (NewsEntity $entity): News => $this->factory->fromEntity($entity), $entities);
    }

    public function getPublishedBySlug(string $slug): News
    {
        $entity = $this->createQueryBuilder('news')
            ->leftJoin('news.categories', 'category')->addSelect('category')
            ->leftJoin('news.tags', 'tag')->addSelect('tag')
            ->where('news.slug = :slug')
            ->andWhere('news.status = :status')
            ->andWhere('news.publishedAt IS NOT NULL')
            ->setParameter('slug', $slug)
            ->setParameter('status', NewsStatus::PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity instanceof NewsEntity) {
            throw NewsNotFoundException::withSlug($slug);
        }

        return $this->factory->fromEntity($entity);
    }

    private function applyPublicFilters(
        \Doctrine\ORM\QueryBuilder $query,
        ?int $categoryId,
        ?int $tagId,
    ): void {
        if ($categoryId !== null) {
            $query->join('news.categories', 'category_filter')
                ->andWhere('category_filter.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($tagId !== null) {
            $query->join('news.tags', 'tag_filter')
                ->andWhere('tag_filter.id = :tagId')
                ->setParameter('tagId', $tagId);
        }
    }
}
