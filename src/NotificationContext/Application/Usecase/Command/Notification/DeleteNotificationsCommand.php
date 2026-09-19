<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Command\Notification;

final readonly class DeleteNotificationsCommand
{
    /** @param list<int> $ids */
    public function __construct(public array $ids)
    {
    }
}
