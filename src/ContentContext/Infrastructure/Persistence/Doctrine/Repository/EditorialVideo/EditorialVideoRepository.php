<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\EditorialVideo;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Domain\Exception\EditorialVideoNotFoundException;
use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Model\EditorialVideoListResult;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\EditorialVideoFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<EditorialVideoEntity> */
final class EditorialVideoRepository extends ServiceEntityRepository implements EditorialVideoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly EditorialVideoFactory $factory) { parent::__construct($registry, EditorialVideoEntity::class); }
    public function save(EditorialVideo $video): EditorialVideo
    {
        $entity = $video->id > 0 ? $this->find($video->id) : null;
        $tags = array_values(array_filter(array_map(fn (Tag $tag): ?TagEntity => $this->getEntityManager()->find(TagEntity::class, $tag->id), $video->tags)));
        $entity = $this->factory->toEntity($video, $entity, $tags);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $video->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }
    public function getById(int $id): EditorialVideo { $entity = $this->find($id); if (!$entity instanceof EditorialVideoEntity) { throw EditorialVideoNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }
    public function delete(EditorialVideo $video): void { $entity = $this->find($video->id); if (!$entity instanceof EditorialVideoEntity) { throw EditorialVideoNotFoundException::withId($video->id); } DbLogListener::disable(); try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); } }
    public function slugExists(string $slug, ?int $exceptId = null): bool { $qb = $this->createQueryBuilder('video')->select('COUNT(video.id)')->where('video.slug = :slug')->setParameter('slug', $slug); if ($exceptId !== null) { $qb->andWhere('video.id != :exceptId')->setParameter('exceptId', $exceptId); } return (int) $qb->getQuery()->getSingleScalarResult() > 0; }
    /**
     * @param list<int> $ids
     * @return list<EditorialVideo>
     */
    public function findByIds(array $ids): array { if ($ids === []) { return []; } return array_map(fn (EditorialVideoEntity $entity): EditorialVideo => $this->factory->fromEntity($entity), $this->createQueryBuilder('video')->andWhere('video.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()); }
    public function list(?string $search, ?EditorialVideoStatus $status, ?VideoProvider $provider, ?int $tagId, int $page, int $limit): EditorialVideoListResult
    {
        $qb = $this->createQueryBuilder('video');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(video.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        if ($status !== null) { $qb->andWhere('video.status = :status')->setParameter('status', $status); }
        if ($provider !== null) { $qb->andWhere('video.provider = :provider')->setParameter('provider', $provider); }
        if ($tagId !== null) { $qb->join('video.tags', 'tag_filter')->andWhere('tag_filter.id = :tagId')->setParameter('tagId', $tagId); }
        $total = (int) (clone $qb)->select('COUNT(video.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('video.updatedAt', 'DESC')->addOrderBy('video.id', 'DESC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new EditorialVideoListResult(array_map(fn (EditorialVideoEntity $entity): EditorialVideo => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function listPublished(int $page, int $limit): EditorialVideoListResult
    {
        $query = $this->createQueryBuilder('video')
            ->where('video.status = :status')
            ->andWhere('video.publishedAt IS NOT NULL')
            ->setParameter('status', EditorialVideoStatus::PUBLISHED);

        $total = (int) (clone $query)
            ->select('COUNT(video.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $entities = $query
            ->orderBy('video.publishedAt', 'DESC')
            ->addOrderBy('video.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new EditorialVideoListResult(array_map(fn (EditorialVideoEntity $entity): EditorialVideo => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }

    public function getPublishedBySlug(string $slug): EditorialVideo
    {
        $entity = $this->createQueryBuilder('video')
            ->leftJoin('video.tags', 'tag')->addSelect('tag')
            ->where('video.slug = :slug')
            ->andWhere('video.status = :status')
            ->andWhere('video.publishedAt IS NOT NULL')
            ->setParameter('slug', $slug)
            ->setParameter('status', EditorialVideoStatus::PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity instanceof EditorialVideoEntity) {
            throw EditorialVideoNotFoundException::withSlug($slug);
        }

        return $this->factory->fromEntity($entity);
    }
}
