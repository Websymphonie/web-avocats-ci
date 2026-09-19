<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Query\Notification;

final class GetNotificationDetailsQuery
{
    public function __construct(public int $notificationId)
    {
    }
}