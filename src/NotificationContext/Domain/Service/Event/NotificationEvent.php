<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Service\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

class NotificationEvent extends Event
{
    /**
     * @param list<string> $roles
     * @param list<int> $userIds
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string                 $title,
        public readonly string                 $message,
        public readonly NotificationTypeEnum   $type,
        public readonly NotificationActionEnum $action,
        public readonly NotificationAccessEnum $access = NotificationAccessEnum::NOTIF_PRIVATE,
        public readonly array                  $roles = [],
        public readonly array                  $userIds = [],
        public readonly array                  $context = [],
    )
    {
    }
}
