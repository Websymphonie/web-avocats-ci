<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Service\Notification;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Service\Notification\NotificationsServiceInterface;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Factory\NotificationFactory;

readonly class NotificationsService implements NotificationsServiceInterface
{
    public function __construct(
        private NotificationDatabaseService $db,
        private NotificationFactory         $factory
    )
    {
    }

    /** @param array<string, mixed> $context */
    public function send(
        string                 $title,
        string                 $message,
        NotificationTypeEnum   $type,
        NotificationActionEnum $action,
        NotificationAccessEnum $access,
        ?User                  $user = null,
        array                  $context = [],
        ?DateTimeImmutable     $readAt = null
    ): void
    {
        $notification = $this->factory->create(
            title: $title,
            message: $message,
            type: $type,
            action: $action,
            access: $access,
            user: $user,
            context: $context,
            readAt: $readAt
        );

        $this->db->persist($notification);
    }
}
