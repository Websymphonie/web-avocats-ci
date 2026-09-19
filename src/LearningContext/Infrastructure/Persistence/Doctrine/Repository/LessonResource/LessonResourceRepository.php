<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LessonResource;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LearningContext\Domain\Exception\LessonResourceNotFoundException;
use Websymphonie\LearningContext\Domain\Model\LessonResource;
use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonResource\LessonResourceEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\LessonResourceFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Domain\Repository\StoredFileRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<LessonResourceEntity> */
final class LessonResourceRepository extends ServiceEntityRepository implements LessonResourceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly LessonResourceFactory $factory, private readonly StoredFileRepositoryInterface $files)
    {
        parent::__construct($registry, LessonResourceEntity::class);
    }

    public function save(LessonResource $resource): LessonResource
    {
        $entity = $resource->id > 0 ? $this->find($resource->id) : null;
        $entity = $this->factory->toEntity($resource, $entity instanceof LessonResourceEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $resource->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity, $this->files->getById($resource->storedFileId));
    }

    public function getById(int $id): LessonResource
    {
        $entity = $this->find($id);
        if (!$entity instanceof LessonResourceEntity) { throw LessonResourceNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity, $this->files->getById($entity->getStoredFileId()));
    }

    public function getByUuid(string $uuid): LessonResource
    {
        $entity = $this->createQueryBuilder('resource')->andWhere('resource.uuid = :uuid')->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)->getQuery()->getOneOrNullResult();
        if (!$entity instanceof LessonResourceEntity) { throw LessonResourceNotFoundException::withUuid($uuid); }
        return $this->factory->fromEntity($entity, $this->files->getById($entity->getStoredFileId()));
    }

    public function getByIdForLesson(int $id, int $lessonId): LessonResource
    {
        $entity = $this->findOneBy(['id' => $id, 'lessonId' => $lessonId]);
        if (!$entity instanceof LessonResourceEntity) { throw LessonResourceNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity, $this->files->getById($entity->getStoredFileId()));
    }

    public function delete(LessonResource $resource): void
    {
        $entity = $this->find($resource->id);
        if (!$entity instanceof LessonResourceEntity) { throw LessonResourceNotFoundException::withId($resource->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }

    /** @return list<LessonResource> */
    public function listByLesson(int $lessonId): array
    {
        $entities = $this->createQueryBuilder('resource')->where('resource.lessonId = :lessonId')->setParameter('lessonId', $lessonId)->orderBy('resource.position', 'ASC')->addOrderBy('resource.id', 'ASC')->getQuery()->getResult();
        return array_map(fn (LessonResourceEntity $entity): LessonResource => $this->factory->fromEntity($entity, $this->files->getById($entity->getStoredFileId())), $entities);
    }

    /** @param list<int> $resourceIds */
    public function reorder(int $lessonId, array $resourceIds): void
    {
        if ($resourceIds === []) { return; }
        $connection = $this->getEntityManager()->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeStatement('UPDATE lesson_resource SET position = position + 1000000 WHERE lesson_id = :lessonId', ['lessonId' => $lessonId]);
            foreach ($resourceIds as $position => $resourceId) {
                $connection->executeStatement('UPDATE lesson_resource SET position = :position WHERE id = :id AND lesson_id = :lessonId', ['position' => $position + 1, 'id' => $resourceId, 'lessonId' => $lessonId]);
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    public function countByLesson(int $lessonId): int
    {
        return (int) $this->createQueryBuilder('resource')->select('COUNT(resource.id)')->where('resource.lessonId = :lessonId')->setParameter('lessonId', $lessonId)->getQuery()->getSingleScalarResult();
    }

    public function countStoredFileUsage(int $storedFileId): int
    {
        return (int) $this->createQueryBuilder('resource')->select('COUNT(resource.id)')->where('resource.storedFileId = :storedFileId')->setParameter('storedFileId', $storedFileId)->getQuery()->getSingleScalarResult();
    }
}
