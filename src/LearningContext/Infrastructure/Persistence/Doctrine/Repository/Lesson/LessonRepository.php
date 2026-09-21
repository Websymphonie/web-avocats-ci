<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\Lesson;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
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

    public function getByUuid(string $uuid): Lesson
    {
        $entity = $this->createQueryBuilder('lesson')
            ->andWhere('lesson.uuid = :uuid')
            ->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)
            ->getQuery()->getOneOrNullResult();
        if (!$entity instanceof LessonEntity) { throw LessonNotFoundException::withUuid($uuid); }
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

    /** @return list<Lesson> */
    public function listByTraining(int $trainingId): array
    {
        $ids = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT lesson.id
             FROM lesson
             INNER JOIN course_module module ON module.id = lesson.module_id
             WHERE module.training_id = :trainingId
             ORDER BY module.position ASC, module.id ASC, lesson.position ASC, lesson.id ASC',
            ['trainingId' => $trainingId],
        )->fetchFirstColumn();
        if ($ids === []) { return []; }

        $entitiesById = [];
        foreach ($this->findBy(['id' => array_map('intval', $ids)]) as $entity) {
            $entitiesById[$entity->getId()] = $entity;
        }
        $entities = array_values(array_filter(array_map(static fn (string|int $id): ?LessonEntity => $entitiesById[(int) $id] ?? null, $ids)));

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

    public function countByTraining(int $trainingId): int
    {
        return (int) $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT COUNT(lesson.id) FROM lesson INNER JOIN course_module ON course_module.id = lesson.module_id WHERE course_module.training_id = :trainingId',
            ['trainingId' => $trainingId],
        );
    }

    /** @param list<int> $trainingIds @return array<int, int> */
    public function countByTrainingIds(array $trainingIds): array
    {
        if ($trainingIds === []) {
            return [];
        }

        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT course_module.training_id AS training_id, COUNT(lesson.id) AS lesson_count
             FROM lesson
             INNER JOIN course_module ON course_module.id = lesson.module_id
             WHERE course_module.training_id IN (?)
             GROUP BY course_module.training_id',
            [$trainingIds],
            [ArrayParameterType::INTEGER],
        )->fetchAllAssociative();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['training_id']] = (int) $row['lesson_count'];
        }

        return $counts;
    }
}
