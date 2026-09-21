<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Repository\LiveTrainingDetails;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\LearningContext\Domain\Exception\LiveTrainingDetailsNotFoundException;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Repository\LiveTrainingDetailsRepositoryInterface;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\LiveTrainingDetailsFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<LiveTrainingDetailsEntity> */
final class LiveTrainingDetailsRepository extends ServiceEntityRepository implements LiveTrainingDetailsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly LiveTrainingDetailsFactory $factory)
    {
        parent::__construct($registry, LiveTrainingDetailsEntity::class);
    }

    public function save(LiveTrainingDetails $details): LiveTrainingDetails
    {
        $entity = $details->id > 0 ? $this->find($details->id) : null;
        $entity = $this->factory->toEntity($details, $entity instanceof LiveTrainingDetailsEntity ? $entity : null);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, $details->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }

        return $this->factory->fromEntity($entity);
    }

    public function findByTrainingId(int $trainingId): ?LiveTrainingDetails
    {
        $entity = $this->findOneBy(['trainingId' => $trainingId]);
        return $entity instanceof LiveTrainingDetailsEntity ? $this->factory->fromEntity($entity) : null;
    }

    /** @param list<int> $trainingIds @return array<int, LiveTrainingDetails> */
    public function findByTrainingIds(array $trainingIds): array
    {
        if ($trainingIds === []) {
            return [];
        }

        $entities = $this->createQueryBuilder('details')
            ->andWhere('details.trainingId IN (:trainingIds)')
            ->setParameter('trainingIds', $trainingIds, ArrayParameterType::INTEGER)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($entities as $entity) {
            if ($entity instanceof LiveTrainingDetailsEntity) {
                $model = $this->factory->fromEntity($entity);
                $result[$model->trainingId] = $model;
            }
        }

        return $result;
    }

    public function getByTrainingId(int $trainingId): LiveTrainingDetails
    {
        return $this->findByTrainingId($trainingId) ?? throw LiveTrainingDetailsNotFoundException::withTrainingId($trainingId);
    }

    public function deleteByTrainingId(int $trainingId): void
    {
        $entity = $this->findOneBy(['trainingId' => $trainingId]);
        if (!$entity instanceof LiveTrainingDetailsEntity) {
            return;
        }

        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }
}
