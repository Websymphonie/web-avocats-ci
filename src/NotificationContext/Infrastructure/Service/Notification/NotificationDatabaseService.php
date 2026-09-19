<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\Service\Notification;

use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

readonly class NotificationDatabaseService
{
    public function __construct(
        private ManagersInterface $manager
    )
    {
    }

    public function persist(Notifications $notification): void
    {
        $this->manager->execute($notification, DbActionEnum::NEW);
    }
}