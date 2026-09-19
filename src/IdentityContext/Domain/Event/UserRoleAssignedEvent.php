<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Event;

final class UserRoleAssignedEvent
{
    public function __construct(
        public int    $userId,
        public string $role,
    )
    {
    }
}