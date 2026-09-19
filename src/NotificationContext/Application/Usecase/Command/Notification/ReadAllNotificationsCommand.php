<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Command\Notification;

final readonly class ReadAllNotificationsCommand
{
    public function __construct(public int $userId)
    {
    }
}