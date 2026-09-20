<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use DateTimeImmutable;
use Websymphonie\ContentContext\Domain\Event\ContentLifecycleEvent;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class ContentLifecycleEventPublisher
{
    public function __construct(
        private EventDispatcher $eventDispatcher,
        private CurrentActorProvider $actorProvider,
    ) {
    }

    public function publish(string $contentType, string $transition, string $uuid, string $title, string $slug, ?DateTimeImmutable $occurredAt = null): void
    {
        $this->eventDispatcher->dispatch([
            new ContentLifecycleEvent(
                contentType: $contentType,
                transition: $transition,
                contentUuid: $uuid,
                title: $title,
                slug: $slug,
                actorUserId: $this->actorProvider->currentUserId(),
                occurredAt: $occurredAt ?? new DateTimeImmutable(),
            ),
        ]);
    }
}
