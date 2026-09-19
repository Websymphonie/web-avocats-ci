<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Model\User;

use DateTimeImmutable;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventEmitterFeature;

final class UserModel
{
    use EventEmitterFeature;

    /** @param list<string>|null $roles */
    public function __construct(
        public ?int    $id = null,
        public ?string $uuid = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?array  $roles = [],
        public ?bool   $enabled = false,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    )
    {
    }
}
