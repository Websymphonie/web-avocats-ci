<?php
declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Exception;

use Webmozart\Assert\Assert;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class LogAssert extends Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message, 'log_context');
    }
}