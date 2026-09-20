<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Repository\Payment;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\PaymentNotFoundException;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\Payment\PaymentEntity;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Factory\PaymentFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<PaymentEntity> */
final class PaymentRepository extends ServiceEntityRepository implements PaymentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager, private readonly PaymentFactory $factory) { parent::__construct($registry, PaymentEntity::class); }

    public function save(Payment $payment): Payment
    {
        $entity = $payment->id > 0 ? $this->find($payment->id) : null;
        $entity = $this->factory->toEntity($payment, $entity instanceof PaymentEntity ? $entity : null);
        DbLogListener::disable();
        try { $this->manager->execute($entity, $payment->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW); } finally { DbLogListener::enable(); }
        return $this->factory->fromEntity($entity);
    }

    public function getById(int $id): Payment
    {
        $entity = $this->find($id);
        if (!$entity instanceof PaymentEntity) { throw PaymentNotFoundException::withUuid((string) $id); }
        return $this->factory->fromEntity($entity);
    }

    public function getByUuid(string $uuid): Payment
    {
        $entity = $this->createQueryBuilder('payment')->andWhere('payment.uuid = :uuid')->setParameter('uuid', Uuid::fromString($uuid), UuidType::NAME)->getQuery()->getOneOrNullResult();
        if (!$entity instanceof PaymentEntity) { throw PaymentNotFoundException::withUuid($uuid); }
        return $this->factory->fromEntity($entity);
    }

    public function findByUserAndIdempotencyKey(int $userId, string $idempotencyKey): ?Payment
    {
        $entity = $this->findOneBy(['userId' => $userId, 'idempotencyKey' => $idempotencyKey]);
        return $entity instanceof PaymentEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function findPendingByUserAndTraining(int $userId, int $trainingId): ?Payment
    {
        $entity = $this->findOneBy(['userId' => $userId, 'trainingId' => $trainingId, 'status' => PaymentStatus::PENDING]);
        return $entity instanceof PaymentEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function findByProviderReference(string $provider, string $reference): ?Payment
    {
        $entity = $this->findOneBy(['provider' => $provider, 'providerReference' => $reference]);
        return $entity instanceof PaymentEntity ? $this->factory->fromEntity($entity) : null;
    }

    public function list(int $page, int $limit): array
    {
        $entities = $this->createQueryBuilder('payment')->orderBy('payment.createdAt', 'DESC')->addOrderBy('payment.id', 'DESC')->setFirstResult(max(0, $page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return array_map(fn (PaymentEntity $entity): Payment => $this->factory->fromEntity($entity), $entities);
    }
}
