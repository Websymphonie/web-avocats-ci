<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Event;

final readonly class ResetCreatedEvent
{
    public function __construct(public int $userId, public string $selector, public string $secret, public \DateTimeImmutable $expiresAt)
    {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
