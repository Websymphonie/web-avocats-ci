<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Repository\ContactMessage;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Exception\ContactMessageNotFoundException;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Model\ContactMessageListResult;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;
use Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Entity\ContactMessage\ContactMessageEntity;
use Websymphonie\ContactContext\Infrastructure\Persistence\Factory\ContactMessageFactory;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/** @extends ServiceEntityRepository<ContactMessageEntity> */
final class ContactMessageRepository extends ServiceEntityRepository implements ContactMessageRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ManagersInterface $manager,
        private readonly ContactMessageFactory $factory,
    ) {
        parent::__construct($registry, ContactMessageEntity::class);
    }

    public function save(ContactMessage $message): ContactMessage
    {
        $entity = $message->id > 0 ? $this->find($message->id) : null;
        $entity = $this->factory->toEntity($message, $entity instanceof ContactMessageEntity ? $entity : null);
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, $message->id > 0 ? DbActionEnum::EDIT : DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }

        return $this->factory->fromEntity($entity);
    }

    public function getByUuid(string $uuid): ContactMessage
    {
        try {
            $identifier = Uuid::fromString($uuid);
        } catch (\Throwable) {
            throw ContactMessageNotFoundException::withUuid($uuid);
        }

        $entity = $this->createQueryBuilder('message')
            ->andWhere('message.uuid = :uuid')
            ->setParameter('uuid', $identifier, UuidType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity instanceof ContactMessageEntity) {
            throw ContactMessageNotFoundException::withUuid($uuid);
        }

        return $this->factory->fromEntity($entity);
    }

    public function claimForRetry(string $uuid): ContactMessage
    {
        try {
            $identifier = Uuid::fromString($uuid);
        } catch (\Throwable) {
            throw ContactMessageNotFoundException::withUuid($uuid);
        }

        $entityManager = $this->getEntityManager();
        $entityManager->beginTransaction();

        try {
            $entity = $this->createQueryBuilder('message')
                ->andWhere('message.uuid = :uuid')
                ->setParameter('uuid', $identifier, UuidType::NAME)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getOneOrNullResult();

            if (!$entity instanceof ContactMessageEntity) {
                throw ContactMessageNotFoundException::withUuid($uuid);
            }

            $message = $this->factory->fromEntity($entity);
            $message->prepareForRetry();
            $this->factory->toEntity($message, $entity);

            DbLogListener::disable();
            try {
                $entityManager->flush();
            } finally {
                DbLogListener::enable();
            }

            $entityManager->commit();

            return $message;
        } catch (\Throwable $exception) {
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->rollback();
            }

            throw $exception;
        }
    }

    public function list(?ContactMessageDeliveryStatus $status, int $page, int $limit): ContactMessageListResult
    {
        $queryBuilder = $this->createQueryBuilder('message');

        if ($status !== null) {
            $queryBuilder
                ->andWhere('message.deliveryStatus = :status')
                ->setParameter('status', $status);
        }

        $total = (int) (clone $queryBuilder)
            ->select('COUNT(message.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $entities = $queryBuilder
            ->orderBy('message.submittedAt', 'DESC')
            ->addOrderBy('message.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new ContactMessageListResult(
            array_map(fn (ContactMessageEntity $entity): ContactMessage => $this->factory->fromEntity($entity), $entities),
            $total,
            $page,
            $limit,
        );
    }
}
