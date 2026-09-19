<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\AuthLog;

use DateTimeImmutable;

class AuthLogModel
{
    public function __construct(
        public ?int               $id = null,
        public ?string            $uuid = null,
        public ?string            $userIp = null,
        public ?string            $emailEntered = null,
        public ?bool              $isSuccessFulAuth = null,
        public ?DateTimeImmutable $startOfBlackListing = null,
        public ?DateTimeImmutable $endOfBlackListing = null,
        public ?bool              $isRememberMeAuth = null,
        public ?DateTimeImmutable $deauthenticatedAt = null,
        public ?DateTimeImmutable $authAttemptAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    )
    {
    }
}