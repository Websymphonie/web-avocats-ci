<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Infrastructure\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\IdentityContext\Domain\Event\PasswordChangedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentActivatedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentRevokedEvent;
use Websymphonie\PaymentContext\Domain\Event\PaymentFailedEvent;
use Websymphonie\NotificationContext\Application\Usecase\Notification\SendNotificationUseCase;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;

final readonly class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SendNotificationUseCase $sendNotificationUseCase,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordChangedEvent::class => 'onPasswordChanged',
            EnrollmentActivatedEvent::class => 'onEnrollmentActivated',
            EnrollmentRevokedEvent::class => 'onEnrollmentRevoked',
            PaymentFailedEvent::class => 'onPaymentFailed',
        ];
    }

    public function onPasswordChanged(PasswordChangedEvent $event): void
    {
        $this->sendSafely(
            eventType: PasswordChangedEvent::class,
            businessReference: (string) $event->userId,
            userId: $event->userId,
            callback: function () use ($event): void {
                $message = $event->actorUserId === $event->userId
                    ? 'Vous avez modifié votre mot de passe.'
                    : 'Votre mot de passe à été modifié.';

                ($this->sendNotificationUseCase)->execute(
                    title: 'Mot de passe',
                    message: $message,
                    type: NotificationTypeEnum::NOTIF_PASSWORD,
                    action: NotificationActionEnum::NOTIF_UPDATE,
                    access: NotificationAccessEnum::NOTIF_PRIVATE,
                    userIds: [$event->userId],
                    context: ['userId' => $event->userId, 'actorUserId' => $event->actorUserId],
                );
            },
        );
    }

    public function onEnrollmentActivated(EnrollmentActivatedEvent $event): void
    {
        $this->sendSafely(
            eventType: EnrollmentActivatedEvent::class,
            businessReference: $event->enrollmentUuid,
            userId: $event->userId,
            callback: function () use ($event): void {
                $isPayment = $event->source === 'PAYMENT';
                $isAdminGrant = $event->source === 'ADMIN_GRANT';
                $title = $isPayment ? 'Paiement confirmé' : ($isAdminGrant ? 'Accès à une formation' : 'Inscription confirmée');
                $message = $isPayment
                    ? 'Votre paiement est confirmé et votre accès à la formation « ' . $event->trainingTitle . ' » est activé.'
                    : ($isAdminGrant
                        ? 'Un accès à la formation « ' . $event->trainingTitle . ' » vous a été accordé.'
                        : 'Votre inscription à la formation « ' . $event->trainingTitle . ' » est confirmée.');

                ($this->sendNotificationUseCase)->execute(
                    title: $title,
                    message: $message,
                    type: NotificationTypeEnum::NOTIF_INFO,
                    action: NotificationActionEnum::NOTIF_ADD,
                    access: NotificationAccessEnum::NOTIF_PRIVATE,
                    userIds: [$event->userId],
                    context: [
                        'enrollmentUuid' => $event->enrollmentUuid,
                        'trainingId' => $event->trainingId,
                        'source' => $event->source,
                    ],
                    deduplicationKey: sprintf('learning.enrollment.activated|%s|%s', $event->enrollmentUuid, $event->activationReference),
                );
            },
        );
    }

    public function onEnrollmentRevoked(EnrollmentRevokedEvent $event): void
    {
        $this->sendSafely(
            eventType: EnrollmentRevokedEvent::class,
            businessReference: $event->enrollmentUuid,
            userId: $event->userId,
            callback: function () use ($event): void {
                ($this->sendNotificationUseCase)->execute(
                    title: 'Accès à la formation révoqué',
                    message: 'Votre accès à la formation « ' . $event->trainingTitle . ' » a été révoqué.',
                    type: NotificationTypeEnum::NOTIF_INFO,
                    action: NotificationActionEnum::NOTIF_UPDATE,
                    access: NotificationAccessEnum::NOTIF_PRIVATE,
                    userIds: [$event->userId],
                    context: [
                        'enrollmentUuid' => $event->enrollmentUuid,
                        'trainingId' => $event->trainingId,
                    ],
                    deduplicationKey: sprintf('learning.enrollment.revoked|%s|%s', $event->enrollmentUuid, $event->revocationReference),
                );
            },
        );
    }

    public function onPaymentFailed(PaymentFailedEvent $event): void
    {
        $this->sendSafely(
            eventType: PaymentFailedEvent::class,
            businessReference: $event->paymentUuid,
            userId: $event->userId,
            callback: function () use ($event): void {
                ($this->sendNotificationUseCase)->execute(
                    title: 'Paiement non confirmé',
                    message: "Votre paiement pour la formation « {$event->trainingTitle} » n'a pas pu être confirmé.",
                    type: NotificationTypeEnum::NOTIF_INFO,
                    action: NotificationActionEnum::NOTIF_UPDATE,
                    access: NotificationAccessEnum::NOTIF_PRIVATE,
                    userIds: [$event->userId],
                    context: [
                        'paymentUuid' => $event->paymentUuid,
                        'trainingId' => $event->trainingId,
                        'amount' => $event->amount,
                        'currency' => $event->currency,
                    ],
                    deduplicationKey: sprintf('payment.failed|%s', $event->paymentUuid),
                );
            },
        );
    }

    private function sendSafely(string $eventType, string $businessReference, int $userId, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            $this->logger->error('La notification transactionnelle n\'a pas pu être persistée.', [
                'eventType' => $eventType,
                'businessReference' => $businessReference,
                'userId' => $userId,
                'failureClass' => $exception::class,
            ]);
        }
    }
}
