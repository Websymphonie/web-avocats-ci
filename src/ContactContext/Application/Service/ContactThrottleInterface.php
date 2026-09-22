<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

interface ContactThrottleInterface
{
    public function consume(?string $ip): bool;
}
