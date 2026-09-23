<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Page;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Exception\PageNotFoundException;
use Websymphonie\ContentContext\Domain\Model\Page;
use Websymphonie\ContentContext\Domain\Model\PageListResult;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\PageFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<PageEntity> */
final class PageRepository extends ServiceEntityRepository implements PageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly PageFactory $factory)
    {
        parent::__construct($registry, PageEntity::class);
    }

    public function save(Page $page): Page
    {
        $entity = $page->id > 0 ? $this->find($page->id) : null;
        $entity = $this->factory->toEntity($page, $entity instanceof PageEntity ? $entity : null);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, $page->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): Page
    {
        $entity = $this->find($id);
        if (!$entity instanceof PageEntity) {
            throw PageNotFoundException::withId($id);
        }
        return $this->factory->fromEntity($entity);
    }

    public function delete(Page $page): void
    {
        $entity = $this->find($page->id);
        if (!$entity instanceof PageEntity) {
            throw PageNotFoundException::withId($page->id);
        }
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    /** @param list<Page> $pages */
    public function deleteMany(array $pages): void
    {
        $entities = [];
        foreach ($pages as $page) {
            $entity = $this->find($page->id);
            if (!$entity instanceof PageEntity) {
                throw PageNotFoundException::withId($page->id);
            }
            $entities[] = $entity;
        }

        DbLogListener::disable();
        try {
            $this->getEntityManager()->wrapInTransaction(function () use ($entities): void {
                foreach ($entities as $entity) {
                    $this->getEntityManager()->remove($entity);
                }
                $this->getEntityManager()->flush();
            });
        } finally {
            DbLogListener::enable();
        }
    }

    public function countMediaUsage(int $mediaId): int
    {
        return (int) $this->createQueryBuilder('page')
            ->select('COUNT(page.id)')
            ->where('page.coverMediaId = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->getQuery()->getSingleScalarResult();
    }

    public function slugExists(string $slug, ?int $exceptId = null, ?PageGroup $group = null): bool
    {
        $query = $this->createQueryBuilder('page')
            ->select('COUNT(page.id)')
            ->where('page.slug = :slug')
            ->setParameter('slug', $slug);
        if ($exceptId !== null) {
            $query->andWhere('page.id != :exceptId')->setParameter('exceptId', $exceptId);
        }
        if ($group !== null) {
            $query->andWhere('page.editorialGroup = :group')->setParameter('group', $group);
        }
        return (int) $query->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param list<int> $ids
     * @return list<Page>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        return array_map(
            fn (PageEntity $entity): Page => $this->factory->fromEntity($entity),
            $this->createQueryBuilder('page')->andWhere('page.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult(),
        );
    }

    public function list(?string $search, ?PageStatus $status, int $page, int $limit): PageListResult
    {
        $query = $this->createQueryBuilder('page');
        if ($search !== null && trim($search) !== '') {
            $query->andWhere('LOWER(page.title) LIKE LOWER(:search) OR LOWER(page.slug) LIKE LOWER(:search)')
                ->setParameter('search', '%' . trim($search) . '%');
        }
        if ($status !== null) {
            $query->andWhere('page.status = :status')->setParameter('status', $status);
        }
        $total = (int) (clone $query)->select('COUNT(page.id)')->getQuery()->getSingleScalarResult();
        $entities = $query->orderBy('page.updatedAt', 'DESC')->addOrderBy('page.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new PageListResult(array_map(fn (PageEntity $entity): Page => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function findPublishedBySlug(string $slug, ?PageGroup $group = null): ?Page
    {
        $query = $this->createQueryBuilder('page')
            ->andWhere('page.slug = :slug')
            ->andWhere('page.status = :status')
            ->andWhere('page.publishedAt IS NOT NULL')
            ->setParameter('slug', $slug)
            ->setParameter('status', PageStatus::PUBLISHED);
        if ($group !== null) {
            $query->andWhere('page.editorialGroup = :group')->setParameter('group', $group);
        }
        $entity = $query->getQuery()->getOneOrNullResult();
        return $entity instanceof PageEntity ? $this->factory->fromEntity($entity) : null;
    }

    /** @return list<Page> */
    public function searchPublished(string $term, int $limit): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $entities = $this->createQueryBuilder('page')
            ->where('page.status = :status')
            ->andWhere('page.publishedAt IS NOT NULL')
            ->andWhere('(LOWER(page.title) LIKE LOWER(:term) OR LOWER(page.slug) LIKE LOWER(:term) OR LOWER(page.content) LIKE LOWER(:term))')
            ->setParameter('status', PageStatus::PUBLISHED)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('LOWER(page.title)', 'ASC')
            ->addOrderBy('LOWER(page.slug)', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();

        return array_map(fn (PageEntity $entity): Page => $this->factory->fromEntity($entity), $entities);
    }

    /** @return list<Page> */
    public function findPublishedByGroup(PageGroup $group): array
    {
        $entities = $this->createQueryBuilder('page')
            ->andWhere('page.editorialGroup = :group')
            ->andWhere('page.status = :status')
            ->andWhere('page.publishedAt IS NOT NULL')
            ->setParameter('group', $group)
            ->setParameter('status', PageStatus::PUBLISHED)
            ->orderBy('page.sortOrder', 'ASC')
            ->addOrderBy('LOWER(page.title)', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (PageEntity $entity): Page => $this->factory->fromEntity($entity), $entities);
    }
}
