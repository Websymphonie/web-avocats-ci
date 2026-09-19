<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Repository\Notifications;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

/**
 * @extends ServiceEntityRepository<Notifications>
 */
class NotificationsRepository extends ServiceEntityRepository implements NotificationModelRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ManagersInterface $manager)
    {
        parent::__construct($registry, Notifications::class);
    }

    public function create(Notifications $entity): Notifications
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::NEW);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }

    public function remove(Notifications $entity): void
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::DELETE);
        } finally {
            DbLogListener::enable();
        }
    }

    public function getById(int $id): Notifications
    {
        $notification = $this->find($id);

        if ($notification === null) {
            throw NotificationNotFound::withId($id);
        }

        return $notification;
    }

    public function getAccessibleById(int $id, User $user): Notifications
    {
        $notification = $this->createQueryBuilder('notification')
            ->andWhere('notification.id = :id')
            ->andWhere('notification.access = :public OR notification.user = :user')
            ->setParameter('id', $id)
            ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$notification instanceof Notifications) {
            throw NotificationNotFound::withId($id);
        }

        return $notification;
    }

    /** @return list<Notifications> */
    public function getUnreadNotifs(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.readAt IS NULL');

        if ($user !== null) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'n.access = :public',
                    'n.user = :user'
                )
            )
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC->value)
                ->setParameter('user', $user);
        } else {
            $qb->andWhere('n.access = :public')
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC->value);
        }

        return $qb->addOrderBy('n.id', 'DESC')
            ->setMaxResults(5) // Optionnel : limite pour un menu déroulant
            ->getQuery()
            ->getResult();
    }

    /** @return list<Notifications> */
    public function findUnreadForUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.readAt IS NULL')
            ->andWhere(
                $this->createQueryBuilder('n')->expr()->orX(
                    'n.access = :public',
                    'n.user = :user'
                )
            )
            ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC->value)
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function countNotifs(?User $user = null): int
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.readAt IS NULL');

        if ($user !== null) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'n.access = :public',
                    'n.user = :user'
                )
            )
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC->value)
                ->setParameter('user', $user);
        } else {
            // utilisateur non connecté
            $qb->andWhere('n.access = :public')
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC->value);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /** @return Query<mixed, mixed> */
    public function getNotificationQuery(GetNotificationListQuery $query): Query
    {
        $qb = $this->createQueryBuilder('n');
        if ($query->message !== null) {
            $qb = $qb->andWhere('n.message LIKE :message')
                ->setParameter('message', '%' . $query->message . '%');
        }
        if ($query->action !== null) {
            $qb = $qb->andWhere('n.action = :action')
                ->setParameter('action', $query->action);
        }
        if ($query->type !== null) {
            $qb = $qb->andWhere('n.type = :type')
                ->setParameter('type', $query->type);
        }
        if ($query->access !== null) {
            $qb = $qb->andWhere('n.access = :access')
                ->setParameter('access', $query->access);
        }

        if ($query->user !== null) {
            $qb->andWhere('n.access = :public OR n.user = :user')
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC)
                ->setParameter('user', $query->user);
        } else {
            // Cas anonyme ou sécurité désactivée : ne montrer que les publiques
            $qb->andWhere('n.access = :public')
                ->setParameter('public', NotificationAccessEnum::NOTIF_PUBLIC);
        }

        return $qb->addOrderBy('n.id', 'DESC')->getQuery();
    }

    public function markAllAsReadByUser(int $userId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.readAt', ':now')
            ->where('n.user = :userId')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    public function update(Notifications $entity): Notifications
    {
        DbLogListener::disable();
        try {
            $this->manager->execute($entity, DbActionEnum::EDIT);
        } finally {
            DbLogListener::enable();
        }
        return $entity;
    }
}
