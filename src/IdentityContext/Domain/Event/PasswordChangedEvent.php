<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Event;

final readonly class PasswordChangedEvent
{
    public function __construct(
        public int $userId,
        public int $actorUserId,
    ) {
    }
}
