<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\PaymentContext\Domain\Event\PaymentConfirmedEvent;
use Websymphonie\PaymentContext\Domain\Event\PaymentFailedEvent;

final readonly class PaymentAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(private BusinessAuditRecorder $recorder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentConfirmedEvent::class => 'onPaymentConfirmed',
            PaymentFailedEvent::class => 'onPaymentFailed',
        ];
    }

    public function onPaymentConfirmed(PaymentConfirmedEvent $event): void
    {
        $this->recordPayment(
            eventType: PaymentConfirmedEvent::class,
            action: 'payment.payment.confirmed',
            paymentUuid: $event->paymentUuid,
            userId: $event->userId,
            trainingId: $event->trainingId,
            amount: $event->amount,
            currency: $event->currency,
            provider: $event->provider,
            providerReference: $event->providerReference,
            occurredAt: $event->confirmedAt,
        );
    }

    public function onPaymentFailed(PaymentFailedEvent $event): void
    {
        $this->recordPayment(
            eventType: PaymentFailedEvent::class,
            action: 'payment.payment.failed',
            paymentUuid: $event->paymentUuid,
            userId: $event->userId,
            trainingId: $event->trainingId,
            amount: $event->amount,
            currency: $event->currency,
            provider: $event->provider,
            providerReference: $event->providerReference,
            occurredAt: $event->failedAt ?? new \DateTimeImmutable(),
        );
    }

    private function recordPayment(string $eventType, string $action, string $paymentUuid, int $userId, int $trainingId, int $amount, string $currency, string $provider, ?string $providerReference, \DateTimeImmutable $occurredAt): void
    {
        $this->recorder->record(
            eventType: $eventType,
            context: 'PAYMENT',
            action: $action,
            actorType: AuditActorType::SYSTEM,
            actorId: strtolower($provider) . '_webhook',
            targetType: 'Payment',
            targetId: $paymentUuid,
            metadata: ['provider' => $provider, 'amount' => $amount, 'currency' => $currency, 'providerReference' => $providerReference, 'trainingId' => $trainingId, 'learner_user_id' => $userId],
            occurredAt: $occurredAt,
            deduplicationKey: hash('sha256', implode('|', [$action, $paymentUuid, $occurredAt->format('Y-m-d\\TH:i:s.uP')])),
            businessReference: $paymentUuid,
        );
    }
}
