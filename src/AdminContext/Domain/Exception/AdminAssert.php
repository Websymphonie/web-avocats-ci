<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Exception;

use Webmozart\Assert\Assert;
use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class AdminAssert extends Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message, 'admin_context');
    }
}