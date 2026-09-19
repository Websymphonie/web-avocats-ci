<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Repository\Notification;

use Doctrine\ORM\Query;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

interface NotificationModelRepository
{
    public function create(Notifications $entity): Notifications;

    public function update(Notifications $entity): Notifications;

    public function remove(Notifications $entity): void;

    public function getById(int $id): Notifications;

    public function getAccessibleById(int $id, User $user): Notifications;

    /**
     * @param list<int> $ids
     * @return list<Notifications>
     */
    public function findAccessibleByIds(array $ids, User $user): array;

    /** @return list<Notifications> */
    public function getUnreadNotifs(?User $user): array;

    public function countNotifs(?User $user): int;

    /** @return Query<mixed, mixed> */
    public function getNotificationQuery(GetNotificationListQuery $query): Query;

    public function markAllAsReadByUser(int $userId): void;
}
