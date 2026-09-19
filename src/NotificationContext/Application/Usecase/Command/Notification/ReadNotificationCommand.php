<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Command\Notification;

final readonly class ReadNotificationCommand
{
    public function __construct(public int $id)
    {
    }
}