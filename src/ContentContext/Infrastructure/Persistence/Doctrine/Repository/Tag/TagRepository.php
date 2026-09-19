<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Tag;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Exception\TagNotFoundException;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Model\TagListResult;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery\PhotoGalleryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\TagFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<TagEntity> */
final class TagRepository extends ServiceEntityRepository implements TagRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly TagFactory $factory)
    {
        parent::__construct($registry, TagEntity::class);
    }

    public function save(Tag $tag): Tag
    {
        $entity = $tag->id > 0 ? $this->find($tag->id) : null;
        $entity = $this->factory->toEntity($tag, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $tag->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); }
        finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): Tag
    {
        $entity = $this->find($id);
        if (!$entity instanceof TagEntity) { throw TagNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    /** @param list<int> $ids @return list<Tag> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) { return []; }
        return array_map(fn (TagEntity $entity): Tag => $this->factory->fromEntity($entity), $this->createQueryBuilder('tag')->andWhere('tag.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult());
    }

    public function list(?string $search, int $page, int $limit): TagListResult
    {
        $qb = $this->createQueryBuilder('tag');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(tag.name) LIKE LOWER(:search) OR LOWER(tag.slug) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        $total = (int) (clone $qb)->select('COUNT(tag.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('tag.name', 'ASC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new TagListResult(array_map(fn (TagEntity $entity): Tag => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $qb = $this->createQueryBuilder('tag')->select('COUNT(tag.id)')->where('tag.slug = :slug')->setParameter('slug', $slug);
        if ($exceptId !== null) { $qb->andWhere('tag.id != :exceptId')->setParameter('exceptId', $exceptId); }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function countNewsUsage(int $id): int
    {
        $entityManager = $this->getEntityManager();
        $newsCount = (int) $entityManager->createQueryBuilder()->select('COUNT(news.id)')->from(NewsEntity::class, 'news')->join('news.tags', 'news_tag')->where('news_tag.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
        $eventCount = (int) $entityManager->createQueryBuilder()->select('COUNT(event.id)')->from(EventEntity::class, 'event')->join('event.tags', 'event_tag')->where('event_tag.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
        $videoCount = (int) $entityManager->createQueryBuilder()->select('COUNT(video.id)')->from(EditorialVideoEntity::class, 'video')->join('video.tags', 'video_tag')->where('video_tag.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();
        $galleryCount = (int) $entityManager->createQueryBuilder()->select('COUNT(gallery.id)')->from(PhotoGalleryEntity::class, 'gallery')->join('gallery.tags', 'gallery_tag')->where('gallery_tag.id = :id')->setParameter('id', $id)->getQuery()->getSingleScalarResult();

        return $newsCount + $eventCount + $videoCount + $galleryCount;
    }

    public function delete(Tag $tag): void
    {
        $entity = $this->find($tag->id);
        if (!$entity instanceof TagEntity) { throw TagNotFoundException::withId($tag->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); }
        finally { DbLogListener::enable(); }
    }
}
