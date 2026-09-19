<?php

declare(strict_types=1);

namespace Websymphonie\Tests\NotificationContext\Application\Usecase\CommandHandler\Notification;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Command\Notification\DeleteNotificationsCommand;
use Websymphonie\NotificationContext\Application\Usecase\CommandHandler\Notification\DeleteNotificationsHandler;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

final class DeleteNotificationsHandlerTest extends TestCase
{
    public function testItDeletesOnlyNotificationsAccessibleToTheCurrentUser(): void
    {
        $user = $this->createMock(User::class);
        $security = $this->createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn($user);
        $first = $this->createMock(Notifications::class);
        $second = $this->createMock(Notifications::class);
        $repository = $this->createMock(NotificationModelRepository::class);
        $repository->expects(self::once())
            ->method('findAccessibleByIds')
            ->with([5, 6, 99], $user)
            ->willReturn([$first, $second]);
        $repository->expects(self::exactly(2))
            ->method('remove')
            ->withConsecutive([$first], [$second]);

        $deletedCount = (new DeleteNotificationsHandler($repository, $security))(
            new DeleteNotificationsCommand([5, 6, 99])
        );

        self::assertSame(2, $deletedCount);
    }
}
