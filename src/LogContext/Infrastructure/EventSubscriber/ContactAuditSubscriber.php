<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\ContactContext\Domain\Event\ContactMessageDeliveryRetryEvent;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final readonly class ContactAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(private BusinessAuditRecorder $recorder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ContactMessageDeliveryRetryEvent::class => 'onDeliveryRetry'];
    }

    public function onDeliveryRetry(ContactMessageDeliveryRetryEvent $event): void
    {
        $result = strtolower($event->newStatus) === 'sent' ? 'succeeded' : 'failed';
        $action = 'contact.message.delivery_retry_' . $result;
        $actorType = $event->actorUserId !== null ? AuditActorType::USER : AuditActorType::SYSTEM;
        $actorId = $event->actorUserId !== null ? (string) $event->actorUserId : 'contact_system';

        $this->recorder->record(
            eventType: ContactMessageDeliveryRetryEvent::class,
            context: 'CONTACT',
            action: $action,
            actorType: $actorType,
            actorId: $actorId,
            targetType: 'ContactMessage',
            targetId: $event->messageUuid,
            metadata: [
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
            ],
            occurredAt: $event->occurredAt,
            deduplicationKey: hash('sha256', implode('|', [$action, $event->messageUuid, $event->occurredAt->format('Y-m-d\\TH:i:s.uP')])),
            businessReference: $event->messageUuid,
        );
    }
}
