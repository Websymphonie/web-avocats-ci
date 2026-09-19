<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Model\Notification;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Domain\Model\User\UserModel;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

class NotificationModel
{
    /** @param array<string, mixed>|null $context */
    public function __construct(
        public ?int                    $id = null,
        public ?string                 $uuid = null,
        public ?string                 $title = null,
        public ?string                 $message = null,
        public ?NotificationTypeEnum   $type = null,
        public ?NotificationAccessEnum $access = null,
        public ?NotificationActionEnum $action = null,
        public ?array                  $context = null,
        public ?DateTimeImmutable      $readAt = null,
        public ?DateTimeImmutable      $createdAt = null,
        public ?DateTimeImmutable      $updatedAt = null,
        public ?UserModel              $user = null,
        public ?bool                   $isRead = null,
    )
    {
    }
}
