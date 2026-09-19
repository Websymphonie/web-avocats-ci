<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\CourseModule;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LearningContext\Domain\Exception\CourseModuleNotFoundException;
use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Domain\Repository\CourseModuleRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\CourseModuleFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<CourseModuleEntity> */
final class CourseModuleRepository extends ServiceEntityRepository implements CourseModuleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly CourseModuleFactory $factory)
    {
        parent::__construct($registry, CourseModuleEntity::class);
    }

    public function save(CourseModule $module): CourseModule
    {
        $entity = $module->id > 0 ? $this->find($module->id) : null;
        $entity = $this->factory->toEntity($module, $entity);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $module->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): CourseModule
    {
        $entity = $this->find($id);
        if (!$entity instanceof CourseModuleEntity) { throw CourseModuleNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    public function getByIdForTraining(int $id, int $trainingId): CourseModule
    {
        $entity = $this->findOneBy(['id' => $id, 'trainingId' => $trainingId]);
        if (!$entity instanceof CourseModuleEntity) { throw CourseModuleNotFoundException::withId($id); }
        return $this->factory->fromEntity($entity);
    }

    public function delete(CourseModule $module): void
    {
        $entity = $this->find($module->id);
        if (!$entity instanceof CourseModuleEntity) { throw CourseModuleNotFoundException::withId($module->id); }
        DbLogListener::disable();
        try { $this->manager->execute($entity, DbActionEnum::DELETE); } finally { DbLogListener::enable(); }
    }

    /** @return list<CourseModule> */
    public function listByTraining(int $trainingId): array
    {
        $entities = $this->createQueryBuilder('module')->where('module.trainingId = :trainingId')->setParameter('trainingId', $trainingId)->orderBy('module.position', 'ASC')->addOrderBy('module.id', 'ASC')->getQuery()->getResult();
        return array_map(fn (CourseModuleEntity $entity): CourseModule => $this->factory->fromEntity($entity), $entities);
    }

    /** @param list<int> $moduleIds */
    public function reorder(int $trainingId, array $moduleIds): void
    {
        if ($moduleIds === []) { return; }
        $connection = $this->getEntityManager()->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeStatement('UPDATE course_module SET position = position + 1000000 WHERE training_id = :trainingId', ['trainingId' => $trainingId]);
            foreach ($moduleIds as $position => $moduleId) {
                $connection->executeStatement('UPDATE course_module SET position = :position WHERE id = :id AND training_id = :trainingId', ['position' => $position + 1, 'id' => $moduleId, 'trainingId' => $trainingId]);
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    public function countByTraining(int $trainingId): int
    {
        return (int) $this->createQueryBuilder('module')->select('COUNT(module.id)')->where('module.trainingId = :trainingId')->setParameter('trainingId', $trainingId)->getQuery()->getSingleScalarResult();
    }

    public function countEmptyByTraining(int $trainingId): int
    {
        $sql = 'SELECT COUNT(module.id) FROM course_module module LEFT JOIN lesson lesson ON lesson.module_id = module.id WHERE module.training_id = :trainingId AND lesson.id IS NULL';
        return (int) $this->getEntityManager()->getConnection()->fetchOne($sql, ['trainingId' => $trainingId]);
    }
}
