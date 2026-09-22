<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class ContactThrottle implements ContactThrottleInterface
{
    public function __construct(private RateLimiterFactory $ipLimiter)
    {
    }

    public function consume(?string $ip): bool
    {
        return $this->ipLimiter->create($ip !== null && $ip !== '' ? $ip : 'unknown')->consume(1)->isAccepted();
    }
}
