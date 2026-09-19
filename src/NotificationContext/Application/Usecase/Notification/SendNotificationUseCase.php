<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\Notification;

use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Service\Notification\NotificationsServiceInterface;

readonly class SendNotificationUseCase
{
    public function __construct(
        private UserModelRepositoryInterface  $userRepository,
        private NotificationsServiceInterface $notificationsService
    )
    {
    }

    /**
     * @param list<string> $roles
     * @param list<int> $userIds
     * @param array<string, mixed> $context
     */
    public function execute(
        string                 $title,
        string                 $message,
        NotificationTypeEnum   $type,
        NotificationActionEnum $action,
        NotificationAccessEnum $access,
        array                  $roles = [],
        array                  $userIds = [],
        array                  $context = []
    ): void
    {
        $users = [];

        if ($roles) {
            $users = $this->userRepository->findByRoles($roles);
        }

        if ($userIds) {
            $users = array_merge($users, $this->userRepository->findByIds($userIds));
        }

        // anti-doublons
        $users = array_values(array_unique(array_map(fn(User $u): int => (int) $u->getId(), $users)));
        $users = $this->userRepository->findByIds($users);

        foreach ($users as $user) {
            $this->notificationsService->send(
                title: $title,
                message: $message,
                type: $type,
                action: $action,
                access: $access,
                user: $user,
                context: $context
            );
        }
    }

}
