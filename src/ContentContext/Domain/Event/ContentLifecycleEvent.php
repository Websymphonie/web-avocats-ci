<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Event;

use DateTimeImmutable;

/** A meaningful editorial lifecycle fact, never a field-by-field update event. */
final readonly class ContentLifecycleEvent
{
    public function __construct(
        public string $contentType,
        public string $transition,
        public string $contentUuid,
        public string $title,
        public string $slug,
        public ?int $actorUserId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }
}
