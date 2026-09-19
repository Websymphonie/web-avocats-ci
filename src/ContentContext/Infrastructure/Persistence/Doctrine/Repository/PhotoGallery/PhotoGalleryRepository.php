<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\PhotoGallery;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Domain\Exception\PhotoGalleryNotFoundException;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Model\PhotoGalleryListResult;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery\PhotoGalleryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery\PhotoGalleryItemEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\PhotoGalleryFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<PhotoGalleryEntity> */
final class PhotoGalleryRepository extends ServiceEntityRepository implements PhotoGalleryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly PhotoGalleryFactory $factory) { parent::__construct($registry, PhotoGalleryEntity::class); }
    public function save(PhotoGallery $gallery): PhotoGallery
    {
        $entity = $gallery->id > 0 ? $this->find($gallery->id) : null;
        $tags = array_values(array_filter(array_map(fn (Tag $tag): ?TagEntity => $this->getEntityManager()->find(TagEntity::class, $tag->id), $gallery->tags)));
        $entity = $this->factory->toEntity($gallery, $entity, $tags);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $gallery->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }
    public function getById(int $id): PhotoGallery { $entity = $this->find($id); if (!$entity instanceof PhotoGalleryEntity) { throw PhotoGalleryNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }
    public function delete(PhotoGallery $gallery): void
    {
        $entity = $this->find($gallery->id);
        if (!$entity instanceof PhotoGalleryEntity) { throw PhotoGalleryNotFoundException::withId($gallery->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }
    public function slugExists(string $slug, ?int $exceptId = null): bool { $qb = $this->createQueryBuilder('gallery')->select('COUNT(gallery.id)')->where('gallery.slug = :slug')->setParameter('slug', $slug); if ($exceptId !== null) { $qb->andWhere('gallery.id != :exceptId')->setParameter('exceptId', $exceptId); } return (int) $qb->getQuery()->getSingleScalarResult() > 0; }
    /**
     * @param list<int> $ids
     * @return list<PhotoGallery>
     */
    public function findByIds(array $ids): array { if ($ids === []) { return []; } return array_map(fn (PhotoGalleryEntity $entity): PhotoGallery => $this->factory->fromEntity($entity), $this->createQueryBuilder('gallery')->andWhere('gallery.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()); }
    public function list(?string $search, ?PhotoGalleryStatus $status, ?int $tagId, int $page, int $limit): PhotoGalleryListResult
    {
        $qb = $this->createQueryBuilder('gallery');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(gallery.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        if ($status !== null) { $qb->andWhere('gallery.status = :status')->setParameter('status', $status); }
        if ($tagId !== null) { $qb->join('gallery.tags', 'tag_filter')->andWhere('tag_filter.id = :tagId')->setParameter('tagId', $tagId); }
        $total = (int) (clone $qb)->select('COUNT(gallery.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('gallery.updatedAt', 'DESC')->addOrderBy('gallery.id', 'DESC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new PhotoGalleryListResult(array_map(fn (PhotoGalleryEntity $entity): PhotoGallery => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }
    public function countMediaUsage(int $mediaId): int { return (int) $this->getEntityManager()->createQueryBuilder()->select('COUNT(item.id)')->from(PhotoGalleryItemEntity::class, 'item')->where('item.mediaId = :mediaId')->setParameter('mediaId', $mediaId)->getQuery()->getSingleScalarResult(); }
}
