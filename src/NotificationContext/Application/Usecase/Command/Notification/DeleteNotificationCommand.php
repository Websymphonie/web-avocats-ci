<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Command\Notification;

final class DeleteNotificationCommand
{
    public function __construct(public int $id)
    {

    }
}