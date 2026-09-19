<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\User;

final readonly class UserDirectoryUser
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $name,
        public string $email,
        public bool $enabled,
    ) {
    }
}
