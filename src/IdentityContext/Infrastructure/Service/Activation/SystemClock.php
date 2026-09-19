<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Service\Activation;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
