<?php

declare(strict_types=1);

namespace Websymphonie\Tests\NotificationContext\Infrastructure\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use Websymphonie\IdentityContext\Domain\Event\PasswordChangedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LearningContext\Domain\Event\EnrollmentActivatedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentRevokedEvent;
use Websymphonie\NotificationContext\Application\Usecase\Notification\SendNotificationUseCase;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\NotificationContext\Domain\Service\Notification\NotificationsServiceInterface;
use Websymphonie\NotificationContext\Infrastructure\EventSubscriber\NotificationSubscriber;
use Websymphonie\PaymentContext\Domain\Event\PaymentFailedEvent;

final class NotificationSubscriberTest extends TestCase
{
    public function testItMapsAllTransactionalEventsToPrivateNotifications(): void
    {
        $users = $this->createMock(UserModelRepositoryInterface::class);
        $users->method('findByIds')->willReturn([$this->user(42)]);
        $notifications = $this->createMock(NotificationsServiceInterface::class);
        $notifications->expects(self::exactly(5))
            ->method('send')
            ->willReturnCallback(function (...$arguments): void {
                static $index = 0;
                $expected = [
                    ['Inscription confirmée', 'Votre inscription à la formation « Formation gratuite » est confirmée.', 'learning.enrollment.activated|enrollment-1|activation-1'],
                    ['Accès à une formation', 'Un accès à la formation « Formation administrée » vous a été accordé.', 'learning.enrollment.activated|enrollment-2|activation-2'],
                    ['Paiement confirmé', 'Votre paiement est confirmé et votre accès à la formation « Formation payée » est activé.', 'learning.enrollment.activated|enrollment-3|activation-3'],
                    ['Accès à la formation révoqué', 'Votre accès à la formation « Formation révoquée » a été révoqué.', 'learning.enrollment.revoked|enrollment-4|revocation-1'],
                    ['Paiement non confirmé', "Votre paiement pour la formation « Formation échouée » n'a pas pu être confirmé.", 'payment.failed|payment-1'],
                ];
                self::assertSame($expected[$index][0], $arguments[0]);
                self::assertSame($expected[$index][1], $arguments[1]);
                self::assertSame(NotificationTypeEnum::NOTIF_INFO, $arguments[2]);
                self::assertSame(NotificationAccessEnum::NOTIF_PRIVATE, $arguments[4]);
                self::assertSame(42, $arguments[5]->getId());
                self::assertSame(self::hashKey($expected[$index][2]), $arguments[8]);
                ++$index;
            });

        $useCase = new SendNotificationUseCase($users, $notifications);
        $subscriber = new NotificationSubscriber($useCase, $this->createMock(LoggerInterface::class));
        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('enrollment-1', 42, 10, 'SELF_SERVICE', 'Formation gratuite', 'activation-1'));
        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('enrollment-2', 42, 10, 'ADMIN_GRANT', 'Formation administrée', 'activation-2'));
        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('enrollment-3', 42, 10, 'PAYMENT', 'Formation payée', 'activation-3'));
        $subscriber->onEnrollmentRevoked(new EnrollmentRevokedEvent('enrollment-4', 42, 10, 'Formation révoquée', 'revocation-1'));
        $subscriber->onPaymentFailed(new PaymentFailedEvent('payment-1', 42, 10, 5000, 'XOF', 'Formation échouée'));
    }

    public function testItPreservesThePasswordNotificationRegression(): void
    {
        $users = $this->createMock(UserModelRepositoryInterface::class);
        $users->method('findByIds')->willReturn([$this->user(7)]);
        $notifications = $this->createMock(NotificationsServiceInterface::class);
        $notifications->expects(self::once())->method('send')->with(
            'Mot de passe',
            'Votre mot de passe à été modifié.',
            NotificationTypeEnum::NOTIF_PASSWORD,
            NotificationActionEnum::NOTIF_UPDATE,
            NotificationAccessEnum::NOTIF_PRIVATE,
            self::isInstanceOf(User::class),
            ['userId' => 7, 'actorUserId' => 3],
            null,
            null,
        );

        (new NotificationSubscriber(new SendNotificationUseCase($users, $notifications), $this->createMock(LoggerInterface::class)))
            ->onPasswordChanged(new PasswordChangedEvent(7, 3));
    }

    public function testNotificationFailureIsLoggedAndDoesNotEscapeTheSubscriber(): void
    {
        $users = $this->createMock(UserModelRepositoryInterface::class);
        $users->method('findByIds')->willReturn([$this->user(42)]);
        $notifications = $this->createMock(NotificationsServiceInterface::class);
        $notifications->method('send')->willThrowException(new \RuntimeException('database unavailable'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::stringContains('notification transactionnelle'),
            self::callback(static fn (array $context): bool => $context['userId'] === 42 && $context['failureClass'] === \RuntimeException::class),
        );

        (new NotificationSubscriber(new SendNotificationUseCase($users, $notifications), $logger))
            ->onPaymentFailed(new PaymentFailedEvent('payment-1', 42, 10, 5000, 'XOF', 'Formation'));
    }

    private function user(int $id): User
    {
        $user = new User();
        (new ReflectionProperty($user, 'id'))->setValue($user, $id);

        return $user;
    }

    private static function hashKey(string $key): string
    {
        return hash('sha256', $key . '|42');
    }
}
