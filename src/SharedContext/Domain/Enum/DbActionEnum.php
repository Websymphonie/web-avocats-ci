<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

enum DbActionEnum: string
{
    case NEW = 'new';
    case EDIT = 'edit';
    case DELETE = 'delete';

    public static function getValue(string $value): DbActionEnum
    {
        return match ($value) {
            self::NEW->value => self::NEW,
            self::EDIT->value => self::EDIT,
            self::DELETE->value => self::DELETE,
            default => null,
        };
    }

    /** @return list<string> */
    public static function getChoices(): array
    {
        return [self::NEW->value, self::EDIT->value, self::DELETE->value];
    }
}
