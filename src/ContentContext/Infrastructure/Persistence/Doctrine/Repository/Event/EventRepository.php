<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\Event;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Exception\EventNotFoundException;
use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Model\EventListResult;
use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\EventFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<EventEntity> */
final class EventRepository extends ServiceEntityRepository implements EventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly EventFactory $factory) { parent::__construct($registry, EventEntity::class); }
    public function save(Event $event): Event { $entity = $event->id > 0 ? $this->find($event->id) : null; $categoryEntities = array_values(array_filter(array_map(fn (EventCategory $category): ?EventCategoryEntity => $this->getEntityManager()->find(EventCategoryEntity::class, $category->id), $event->categories))); $tagEntities = array_values(array_filter(array_map(fn (Tag $tag): ?TagEntity => $this->getEntityManager()->find(TagEntity::class, $tag->id), $event->tags))); $entity = $this->factory->toEntity($event, $entity, $categoryEntities, $tagEntities); DbLogListener::disable(); try { $this->manager->execute($entity, $event->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); } return $this->factory->fromEntity($entity); }
    public function getById(int $id): Event { $entity = $this->find($id); if (!$entity instanceof EventEntity) { throw EventNotFoundException::withId($id); } return $this->factory->fromEntity($entity); }
    public function delete(Event $event): void { $entity = $this->find($event->id); if (!$entity instanceof EventEntity) { throw EventNotFoundException::withId($event->id); } DbLogListener::disable(); try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); } }
    public function slugExists(string $slug, ?int $exceptId = null): bool { $qb = $this->createQueryBuilder('event')->select('COUNT(event.id)')->where('event.slug = :slug')->setParameter('slug', $slug); if ($exceptId !== null) { $qb->andWhere('event.id != :exceptId')->setParameter('exceptId', $exceptId); } return (int) $qb->getQuery()->getSingleScalarResult() > 0; }
    /** @param list<int> $ids @return list<Event> */
    public function findByIds(array $ids): array { if ($ids === []) { return []; } return array_map(fn (EventEntity $entity): Event => $this->factory->fromEntity($entity), $this->createQueryBuilder('event')->andWhere('event.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult()); }
    public function list(?string $search, ?EventStatus $status, ?EventFormat $format, ?int $categoryId, ?int $tagId, int $page, int $limit): EventListResult { $qb = $this->createQueryBuilder('event'); if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(event.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); } if ($status !== null) { $qb->andWhere('event.status = :status')->setParameter('status', $status); } if ($format !== null) { $qb->andWhere('event.format = :format')->setParameter('format', $format); } if ($categoryId !== null) { $qb->join('event.categories', 'category_filter')->andWhere('category_filter.id = :categoryId')->setParameter('categoryId', $categoryId); } if ($tagId !== null) { $qb->join('event.tags', 'tag_filter')->andWhere('tag_filter.id = :tagId')->setParameter('tagId', $tagId); } $total = (int) (clone $qb)->select('COUNT(event.id)')->getQuery()->getSingleScalarResult(); $entities = $qb->orderBy('event.startsAt', 'DESC')->addOrderBy('event.id', 'DESC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult(); return new EventListResult(array_map(fn (EventEntity $entity): Event => $this->factory->fromEntity($entity), $entities), $total, $page, $limit); }
}
