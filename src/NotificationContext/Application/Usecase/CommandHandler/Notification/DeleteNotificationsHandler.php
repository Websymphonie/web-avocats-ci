<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification;

use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\DeleteNotificationsCommand;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteNotificationsHandler implements CommandHandler
{
    public function __construct(
        private NotificationModelRepository $repository,
        private Security $security,
    ) {
    }

    public function __invoke(DeleteNotificationsCommand $command): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw NotificationNotFound::withId($command->ids[0] ?? 0);
        }

        $notifications = $this->repository->findAccessibleByIds($command->ids, $user);
        foreach ($notifications as $notification) {
            $this->repository->remove($notification);
        }

        return count($notifications);
    }
}
