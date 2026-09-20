<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Service\Notification;

use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Domain\Service\Notification\NotificationsServiceInterface;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Factory\NotificationFactory;

readonly class NotificationsService implements NotificationsServiceInterface
{
    public function __construct(
        private NotificationDatabaseService $db,
        private NotificationFactory         $factory,
        private NotificationModelRepository $repository
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
        ?DateTimeImmutable     $readAt = null,
        ?string                $deduplicationKey = null
    ): void
    {
        if ($deduplicationKey !== null && $this->repository->findByDeduplicationKey($deduplicationKey) !== null) {
            return;
        }

        $notification = $this->factory->create(
            title: $title,
            message: $message,
            type: $type,
            action: $action,
            access: $access,
            user: $user,
            context: $context,
            readAt: $readAt,
            deduplicationKey: $deduplicationKey,
        );

        try {
            $this->db->persist($notification);
        } catch (UniqueConstraintViolationException $exception) {
            // The unique database constraint is the final guard for concurrent replays.
            // With a deduplication key, an existing key means the event was already handled.
            if ($deduplicationKey !== null) {
                return;
            }

            throw $exception;
        }
    }
}
