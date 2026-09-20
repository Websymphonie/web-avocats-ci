<?php

declare(strict_types=1);

namespace Websymphonie\Tests\NotificationContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use ReflectionClass;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\UserFactory;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Factory\NotificationFactory;
use Websymphonie\NotificationContext\Infrastructure\Service\Notification\NotificationDatabaseService;
use Websymphonie\NotificationContext\Infrastructure\Service\Notification\NotificationsService;
use Websymphonie\SharedContext\Application\Service\Manager\ManagersInterface;
use Websymphonie\SharedContext\Domain\Enum\DbActionEnum;

final class NotificationsServiceTest extends TestCase
{
    public function testTheSameDeduplicationKeyIsPersistedOnlyOnce(): void
    {
        $manager = $this->createMock(ManagersInterface::class);
        $manager->expects(self::once())->method('execute')->with(self::isInstanceOf(Notifications::class), DbActionEnum::NEW);

        $existing = new Notifications();
        $repository = $this->createMock(NotificationModelRepository::class);
        $repository->expects(self::exactly(2))
            ->method('findByDeduplicationKey')
            ->with('a' . str_repeat('0', 63))
            ->willReturnOnConsecutiveCalls(null, $existing);

        $service = new NotificationsService(
            new NotificationDatabaseService($manager),
            new NotificationFactory(new UserFactory()),
            $repository,
        );

        $arguments = [
            'Titre',
            'Message',
            NotificationTypeEnum::NOTIF_INFO,
            NotificationActionEnum::NOTIF_ADD,
            NotificationAccessEnum::NOTIF_PRIVATE,
            new User(),
            [],
            null,
            'a' . str_repeat('0', 63),
        ];
        $service->send(...$arguments);
        $service->send(...$arguments);
    }

    public function testAConcurrentUniqueConstraintCollisionIsHandledAsAnAlreadyProcessedReplay(): void
    {
        $collision = (new ReflectionClass(UniqueConstraintViolationException::class))->newInstanceWithoutConstructor();
        $manager = $this->createMock(ManagersInterface::class);
        $manager->expects(self::once())->method('execute')->willThrowException($collision);
        $repository = $this->createMock(NotificationModelRepository::class);
        $repository->expects(self::once())->method('findByDeduplicationKey')->willReturn(null);

        $service = new NotificationsService(
            new NotificationDatabaseService($manager),
            new NotificationFactory(new UserFactory()),
            $repository,
        );

        $service->send(
            title: 'Titre',
            message: 'Message',
            type: NotificationTypeEnum::NOTIF_INFO,
            action: NotificationActionEnum::NOTIF_ADD,
            access: NotificationAccessEnum::NOTIF_PRIVATE,
            user: new User(),
            deduplicationKey: 'b' . str_repeat('0', 63),
        );

        self::assertTrue(true);
    }
}
