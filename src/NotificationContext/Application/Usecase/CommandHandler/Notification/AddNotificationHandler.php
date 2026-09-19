<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification;


use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\AddNotificationCommand;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class AddNotificationHandler implements CommandHandler
{
    public function __construct(
        private NotificationModelRepository  $repository,
        private UserModelRepositoryInterface $userRepository,
    )
    {
    }

    public function __invoke(AddNotificationCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        // Création de la notification
        $notif = new Notifications();
        $notif->add($command);
        $notif->setUser($user);
        $this->repository->create($notif);
    }
}