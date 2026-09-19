<?php

declare(strict_types=1);

namespace Websymphonie\Tests\NotificationContext\Application\Usecase\CommandHandler\Notification;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\ReadNotificationCommand;
use Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification\ReadNotificationHandler;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

final class ReadNotificationHandlerTest extends TestCase
{
    public function testItMarksOnlyAnAccessibleNotificationAsRead(): void
    {
        $user = $this->createMock(User::class);
        $security = $this->createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn($user);
        $notification = $this->createMock(Notifications::class);
        $notification->expects(self::once())->method('getReadAt')->willReturn(null);
        $notification->expects(self::once())->method('markAsRead');
        $repository = $this->createMock(NotificationModelRepository::class);
        $repository
            ->expects(self::once())
            ->method('getAccessibleById')
            ->with(12, $user)
            ->willReturn($notification);
        $repository->expects(self::once())->method('update')->with($notification);

        (new ReadNotificationHandler($repository, $security))(new ReadNotificationCommand(12));
    }
}
