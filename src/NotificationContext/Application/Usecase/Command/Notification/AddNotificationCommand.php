<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Command\Notification;

use DateTimeImmutable;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

final readonly class AddNotificationCommand
{
    /** @param array<string, mixed>|null $context */
    public function __construct(
        public string                  $message,
        public ?NotificationTypeEnum   $type = null,
        public ?NotificationAccessEnum $access = null,
        public ?NotificationActionEnum $action = null,
        public ?array                  $context = null,
        public ?DateTimeImmutable      $readAt = null,
        public ?int                    $userId = null,
        public ?string                 $deduplicationKey = null,
    )
    {
    }
}
