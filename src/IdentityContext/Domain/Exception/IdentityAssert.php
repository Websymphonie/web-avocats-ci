<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception;

use Webmozart\Assert\Assert;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class IdentityAssert extends Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message, 'identity_context');
    }
}