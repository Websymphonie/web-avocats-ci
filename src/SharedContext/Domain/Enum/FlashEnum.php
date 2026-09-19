<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

use InvalidArgumentException;

enum FlashEnum: string
{
    case SUCCESS = 'success';
    case INFO = 'info';
    case WARNING = 'warning';
    case DANGER = 'danger';

    public static function getValue(string $value): FlashEnum
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException(sprintf('Unknown flash type "%s".', $value));
    }
}
