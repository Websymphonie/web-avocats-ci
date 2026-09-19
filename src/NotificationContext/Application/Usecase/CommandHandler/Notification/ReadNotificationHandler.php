<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification;

use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadNotificationCommand;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ReadNotificationHandler implements CommandHandler
{
    public function __construct(
        private NotificationModelRepository $repository,
        private Security $security,
    )
    {
    }

    public function __invoke(ReadNotificationCommand $command): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw NotificationNotFound::withId($command->id);
        }

        $notification = $this->repository->getAccessibleById($command->id, $user);
        if ($notification->getReadAt() === null) {
            $notification->markAsRead();
            $this->repository->update($notification);
        }
    }
}
