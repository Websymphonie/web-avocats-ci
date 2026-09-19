<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification;

use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadAllNotificationsCommand;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReadAllNotificationsHandler implements CommandHandler
{
    public function __construct(
        private NotificationModelRepository $repository
    )
    {
    }

    public function __invoke(ReadAllNotificationsCommand $command): void
    {
        $this->repository->markAllAsReadByUser($command->userId);
    }
}