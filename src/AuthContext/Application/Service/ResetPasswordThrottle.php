<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Service;

use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class ResetPasswordThrottle
{
    public function __construct(
        private RateLimiterFactory $emailLimiter,
        private RateLimiterFactory $ipLimiter,
    ) {
    }

    public function consume(string $email, ?string $ip): bool
    {
        $emailLimit = $this->emailLimiter->create(strtolower(trim($email)))->consume(1)->isAccepted();
        $ipLimit = $this->ipLimiter->create($ip !== null && $ip !== '' ? $ip : 'unknown')->consume(1)->isAccepted();
        return $emailLimit && $ipLimit;
    }
}
