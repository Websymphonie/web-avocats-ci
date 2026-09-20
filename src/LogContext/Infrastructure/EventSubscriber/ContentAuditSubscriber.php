<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\ContentContext\Domain\Event\ContentLifecycleEvent;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final readonly class ContentAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(private BusinessAuditRecorder $recorder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [ContentLifecycleEvent::class => 'onContentLifecycle'];
    }

    public function onContentLifecycle(ContentLifecycleEvent $event): void
    {
        $contentType = strtolower($event->contentType);
        $transition = strtolower($event->transition);
        $action = sprintf('content.%s.%s', $contentType, $transition);
        $actorType = $event->actorUserId !== null ? AuditActorType::USER : AuditActorType::SYSTEM;
        $actorId = $event->actorUserId !== null ? (string) $event->actorUserId : 'content_system';
        $targetType = match ($event->contentType) {
            'NEWS' => 'News',
            'EVENT' => 'Event',
            'DOCUMENT' => 'DocumentPublication',
            'EDITORIAL_VIDEO' => 'EditorialVideo',
            'PHOTO_GALLERY' => 'PhotoGallery',
            default => 'Content',
        };

        $this->recorder->record(
            eventType: ContentLifecycleEvent::class,
            context: 'CONTENT',
            action: $action,
            actorType: $actorType,
            actorId: $actorId,
            targetType: $targetType,
            targetId: $event->contentUuid,
            metadata: ['title' => $event->title, 'slug' => $event->slug, 'transition' => $event->transition],
            occurredAt: $event->occurredAt,
            deduplicationKey: hash('sha256', implode('|', [$action, $event->contentUuid, $event->occurredAt->format('Y-m-d\\TH:i:s.uP')])),
            businessReference: $event->contentUuid,
        );
    }
}
