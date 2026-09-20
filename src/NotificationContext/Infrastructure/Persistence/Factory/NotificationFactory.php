<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Persistence\Factory;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Model\Notification\NotificationModel;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

final readonly class NotificationFactory
{
    public function __construct(private UserFactory $userFactory)
    {
    }

    /**
     * @param list<Notifications> $entities
     * @return list<NotificationModel>
     */
    public function fromEntityList(array $entities): array
    {
        return array_map(fn(Notifications $notification) => self::fromEntity($notification), $entities);
    }

    public function fromEntity(?Notifications $notification): ?NotificationModel
    {
        if ($notification === null) {
            return null;
        }
        return new NotificationModel(
            id: $notification->getId(),
            uuid: $notification->getUuidAsString(),
            title: $notification->getTitle(),
            message: $notification->getMessage(),
            type: $notification->getType(),
            access: $notification->getAccess(),
            action: $notification->getAction(),
            context: $notification->getContext(),
            readAt: $notification->getReadAt(),
            createdAt: $notification->getCreatedAt(),
            updatedAt: $notification->getUpdatedAt(),
            user: $this->userFactory->fromEntity($notification->getUser()),
            isRead: $notification->getReadAt() !== null
        );
    }

    /** @param array<string, mixed> $context */
    public function create(
        string                 $title,
        string                 $message,
        NotificationTypeEnum   $type,
        NotificationActionEnum $action,
        NotificationAccessEnum $access,
        ?User                  $user,
        array                  $context = [],
        ?DateTimeImmutable     $readAt = null,
        ?string                $deduplicationKey = null
    ): Notifications
    {
        $notification = new Notifications();
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setAction($action);
        $notification->setAccess($access);
        $notification->setContext($context);
        $notification->setUser($user);
        $notification->setReadAt($readAt);
        $notification->setDeduplicationKey($deduplicationKey);

        return $notification;
    }
}
