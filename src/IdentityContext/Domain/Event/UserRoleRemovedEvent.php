<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Event;

final readonly class UserRoleRemovedEvent
{
    public function __construct(
        public int $userId,
        public string $role,
        public ?int $actorUserId = null,
    ) {
    }
}
