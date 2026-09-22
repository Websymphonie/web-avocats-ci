<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Event;

use DateTimeImmutable;

final readonly class ContactMessageDeliveryRetryEvent
{
    public function __construct(
        public string $messageUuid,
        public string $previousStatus,
        public string $newStatus,
        public ?int $actorUserId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }
}
