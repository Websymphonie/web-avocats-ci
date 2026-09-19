<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Exception;

use Webmozart\Assert\Assert;

final class SharedAssert extends Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message, 'shared_context');
    }
}