<?php

declare(strict_types=1);

namespace Websymphonie\Tests\NotificationContext\Domain\EventSubscriber;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Notification\SendNotificationUseCase;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\EventSubscriber\Notification\NotificationSubscriber;
use Websymphonie\NotificationContext\Domain\Service\Event\NotificationEvent;
use Websymphonie\NotificationContext\Domain\Service\Notification\NotificationsServiceInterface;

final class NotificationSubscriberTest extends TestCase
{
    public function testItForwardsNotificationEventsToTheNotificationUseCase(): void
    {
        $user = new User();
        (new ReflectionProperty($user, 'id'))->setValue($user, 12);
        $users = $this->createMock(UserModelRepositoryInterface::class);
        $users->expects(self::exactly(2))
            ->method('findByIds')
            ->with([12])
            ->willReturn([$user]);
        $notifications = $this->createMock(NotificationsServiceInterface::class);
        $notifications->expects(self::once())
            ->method('send')
            ->with(
                'Titre',
                'Message',
                NotificationTypeEnum::NOTIF_INFO,
                NotificationActionEnum::NOTIF_ADD,
                NotificationAccessEnum::NOTIF_PRIVATE,
                $user,
                ['source' => 'test'],
            );
        $useCase = new SendNotificationUseCase($users, $notifications);

        (new NotificationSubscriber($useCase))->onSendNotification(new NotificationEvent(
            title: 'Titre',
            message: 'Message',
            type: NotificationTypeEnum::NOTIF_INFO,
            action: NotificationActionEnum::NOTIF_ADD,
            access: NotificationAccessEnum::NOTIF_PRIVATE,
            roles: ['ROLE_ADMIN'],
            userIds: [12],
            context: ['source' => 'test'],
        ));
    }
}
