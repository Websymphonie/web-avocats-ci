<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Query\Notification;

use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

final class GetNotificationListQuery
{
    public function __construct(
        public ?string                 $message = null,
        public ?NotificationActionEnum $action = null,
        public ?NotificationTypeEnum   $type = null,
        public ?NotificationAccessEnum $access = null,
        public ?User                   $user = null,
        public int                     $page = 1,
        public int                     $limit = 15,
    )
    {
    }
}