<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Event;

use DateTimeImmutable;

final readonly class AccountActivationRequestedEvent
{
    public function __construct(
        public int $userId,
        public string $selector,
        public string $token,
        public DateTimeImmutable $expiresAt,
    ) {
    }
}
