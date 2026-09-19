<?php declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Service\Notification;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

interface NotificationsServiceInterface
{
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
    ): void;
}
