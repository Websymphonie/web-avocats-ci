<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Repository\ContactMessage;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
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
}
