<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Lesson;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LearningContext\Domain\Exception\LessonNotFoundException;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Domain\Repository\LessonRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\LessonFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<LessonEntity> */
final class LessonRepository extends ServiceEntityRepository implements LessonRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly LessonFactory $factory)
    {
        parent::__construct($registry, LessonEntity::class);
    }

    public function save(Lesson $lesson): Lesson
    {
        $entity = $lesson->id > 0 ? $this->find($lesson->id) : null;
        $entity = $this->factory->toEntity($lesson, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $lesson->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): Lesson
    {
        $entity = $this->find($id);
        if (!$entity instanceof LessonEntity) { throw LessonNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    public function getByIdForModule(int $id, int $moduleId): Lesson
    {
        $entity = $this->findOneBy(['id' => $id, 'moduleId' => $moduleId]);
        if (!$entity instanceof LessonEntity) { throw LessonNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    public function delete(Lesson $lesson): void
    {
        $entity = $this->find($lesson->id);
        if (!$entity instanceof LessonEntity) { throw LessonNotFoundException::withId($lesson->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }

    /** @return list<Lesson> */
    public function listByModule(int $moduleId): array
    {
        $entities = $this->createQueryBuilder('lesson')->where('lesson.moduleId = :moduleId')->setParameter('moduleId', $moduleId)->orderBy('lesson.position', 'ASC')->addOrderBy('lesson.id', 'ASC')->getQuery()->getResult();
        return array_map(fn (LessonEntity $entity): Lesson => $this->factory->fromEntity($entity), $entities);
    }

    /** @param list<int> $lessonIds */
    public function reorder(int $moduleId, array $lessonIds): void
    {
        if ($lessonIds === []) { return; }
        $connection = $this->getEntityManager()->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeStatement('UPDATE lesson SET position = position + 1000000 WHERE module_id = :moduleId', ['moduleId' => $moduleId]);
            foreach ($lessonIds as $position => $lessonId) {
                $connection->executeStatement('UPDATE lesson SET position = :position WHERE id = :id AND module_id = :moduleId', ['position' => $position + 1, 'id' => $lessonId, 'moduleId' => $moduleId]);
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    public function countByModule(int $moduleId): int
    {
        return (int) $this->createQueryBuilder('lesson')->select('COUNT(lesson.id)')->where('lesson.moduleId = :moduleId')->setParameter('moduleId', $moduleId)->getQuery()->getSingleScalarResult();
    }
}
