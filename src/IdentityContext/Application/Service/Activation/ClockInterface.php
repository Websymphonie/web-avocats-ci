<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\Activation;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
