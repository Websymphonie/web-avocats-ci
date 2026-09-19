<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Repository\DocumentPublication;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Exception\DocumentPublicationNotFoundException;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Model\DocumentPublicationListResult;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Factory\DocumentPublicationFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<DocumentPublicationEntity> */
final class DocumentPublicationRepository extends ServiceEntityRepository implements DocumentPublicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly DocumentPublicationFactory $factory) { parent::__construct($registry, DocumentPublicationEntity::class); }
    public function save(DocumentPublication $document): DocumentPublication
    {
        $entity = $document->id > 0 ? $this->find($document->id) : null;
        $tags = array_values(array_filter(array_map(fn (Tag $tag): ?TagEntity => $this->getEntityManager()->find(TagEntity::class, $tag->id), $document->tags)));
        $entity = $this->factory->toEntity($document, $entity instanceof DocumentPublicationEntity ? $entity : null, $tags);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $document->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }
    public function getById(int $id): DocumentPublication
    {
        $entity = $this->find($id);
        if (!$entity instanceof DocumentPublicationEntity) { throw DocumentPublicationNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }
    public function getByUuid(string $uuid): DocumentPublication
    {
        try { $identifier = Uuid::fromString($uuid); } catch (\Throwable) { throw DocumentPublicationNotFoundException::withUuid($uuid); }
        $entity = $this->createQueryBuilder('document')->andWhere('document.uuid = :uuid')->setParameter('uuid', $identifier)->getQuery()->getOneOrNullResult();
        if (!$entity instanceof DocumentPublicationEntity) { throw DocumentPublicationNotFoundException::withUuid($uuid); }
        return $this->factory->fromEntity($entity);
    }
    public function delete(DocumentPublication $document): void
    {
        $entity = $this->find($document->id);
        if (!$entity instanceof DocumentPublicationEntity) { throw DocumentPublicationNotFoundException::withId($document->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }
    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $qb = $this->createQueryBuilder('document')->select('COUNT(document.id)')->where('document.slug = :slug')->setParameter('slug', $slug);
        if ($exceptId !== null) { $qb->andWhere('document.id != :exceptId')->setParameter('exceptId', $exceptId); }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
    /** @return list<DocumentPublication> */
    public function findByIds(array $ids): array
    {
        if ($ids === []) { return []; }
        return array_map(fn (DocumentPublicationEntity $entity): DocumentPublication => $this->factory->fromEntity($entity), $this->createQueryBuilder('document')->andWhere('document.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->getResult());
    }
    public function list(?string $search, ?DocumentStatus $status, ?DocumentAccessLevel $accessLevel, ?int $tagId, int $page, int $limit): DocumentPublicationListResult
    {
        $qb = $this->createQueryBuilder('document');
        if ($search !== null && trim($search) !== '') { $qb->andWhere('LOWER(document.title) LIKE LOWER(:search)')->setParameter('search', '%' . trim($search) . '%'); }
        if ($status !== null) { $qb->andWhere('document.status = :status')->setParameter('status', $status); }
        if ($accessLevel !== null) { $qb->andWhere('document.accessLevel = :accessLevel')->setParameter('accessLevel', $accessLevel); }
        if ($tagId !== null) { $qb->join('document.tags', 'tag_filter')->andWhere('tag_filter.id = :tagId')->setParameter('tagId', $tagId); }
        $total = (int) (clone $qb)->select('COUNT(document.id)')->getQuery()->getSingleScalarResult();
        $entities = $qb->orderBy('document.updatedAt', 'DESC')->addOrderBy('document.id', 'DESC')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return new DocumentPublicationListResult(array_map(fn (DocumentPublicationEntity $entity): DocumentPublication => $this->factory->fromEntity($entity), $entities), $total, $page, $limit);
    }
    public function countStoredFileUsage(int $storedFileId): int { return (int) $this->createQueryBuilder('document')->select('COUNT(document.id)')->where('document.storedFileId = :storedFileId')->setParameter('storedFileId', $storedFileId)->getQuery()->getSingleScalarResult(); }
}
