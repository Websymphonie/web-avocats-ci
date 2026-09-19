<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain;

use Websymphonie\SharedContext\Domain\Exception\InvalidArgument;

final class Assert extends \Webmozart\Assert\Assert
{
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgument($message);
    }
}