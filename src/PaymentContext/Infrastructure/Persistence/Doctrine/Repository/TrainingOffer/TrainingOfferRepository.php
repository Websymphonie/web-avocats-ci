<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Repository\TrainingOffer;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\PaymentContext\Domain\Exception\TrainingOfferNotFoundException;
use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Factory\TrainingOfferFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<TrainingOfferEntity> */
final class TrainingOfferRepository extends ServiceEntityRepository implements TrainingOfferRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly TrainingOfferFactory $factory) { parent::__construct($registry, TrainingOfferEntity::class); }

    public function save(TrainingOffer $offer): TrainingOffer
    {
        $entity = $offer->id > 0 ? $this->find($offer->id) : null;
        $entity = $this->factory->toEntity($offer, $entity instanceof TrainingOfferEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $offer->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): TrainingOffer
    {
        $entity = $this->find($id);
        if (!$entity instanceof TrainingOfferEntity) { throw TrainingOfferNotFoundException::withTraining($id); }
        return $this->factory->fromEntity($entity);
    }

    public function findByTrainingId(int $trainingId): ?TrainingOffer
    {
        $entity = $this->findOneBy(['trainingId' => $trainingId]);
        return $entity instanceof TrainingOfferEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function list(int $page, int $limit): array
    {
        $entities = $this->createQueryBuilder('offer')->orderBy('offer.updatedAt', 'DESC')->addOrderBy('offer.id', 'DESC')->setFirstResult(max(0, $page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return array_map(fn (TrainingOfferEntity $entity): TrainingOffer => $this->factory->fromEntity($entity), $entities);
    }
}
