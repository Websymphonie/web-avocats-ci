<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Exception;

use Webmozart\Assert\Assert;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class AuthAssert extends Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message, 'auth_context');
    }
}