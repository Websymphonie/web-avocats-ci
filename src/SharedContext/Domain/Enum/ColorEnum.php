<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Enum;

use InvalidArgumentException;

enum ColorEnum: string
{
    case SUCCESS = 'success';
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case INFO = 'info';
    case WARNING = 'warning';
    case DANGER = 'danger';
    case PURPLE = 'purple';
    case PINK = 'pink';

    public static function getValue(string $value): self
    {
        return match ($value) {
            self::SUCCESS->value => self::SUCCESS,
            self::PRIMARY->value => self::PRIMARY,
            self::SECONDARY->value => self::SECONDARY,
            self::INFO->value => self::INFO,
            self::WARNING->value => self::WARNING,
            self::DANGER->value => self::DANGER,
            self::PURPLE->value => self::PURPLE,
            self::PINK->value => self::PINK,
            default => throw new InvalidArgumentException("Invalid color: $value"),
        };
    }

    public function color(): string
    {
        return $this->value;
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::SUCCESS => 'inline-flex items-center rounded-md bg-green-400/10 px-2 py-1 text-xs font-medium text-green-400 inset-ring inset-ring-green-500/20',
            self::PRIMARY => 'inline-flex items-center rounded-md bg-blue-400/10 px-2 py-1 text-xs font-medium text-blue-400 inset-ring inset-ring-blue-400/30',
            self::SECONDARY => 'inline-flex items-center rounded-md bg-gray-400/10 px-2 py-1 text-xs font-medium text-gray-400 inset-ring inset-ring-gray-400/20',
            self::INFO => 'inline-flex items-center rounded-md bg-indigo-400/10 px-2 py-1 text-xs font-medium text-indigo-400 inset-ring inset-ring-indigo-400/30',
            self::WARNING => 'inline-flex items-center rounded-md bg-yellow-400/10 px-2 py-1 text-xs font-medium text-yellow-500 inset-ring inset-ring-yellow-400/20',
            self::DANGER => 'inline-flex items-center rounded-md bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 inset-ring inset-ring-red-400/20',
            self::PURPLE => 'inline-flex items-center rounded-md bg-purple-400/10 px-2 py-1 text-xs font-medium text-purple-400 inset-ring inset-ring-purple-400/30',
            self::PINK => 'inline-flex items-center rounded-md bg-pink-400/10 px-2 py-1 text-xs font-medium text-pink-400 inset-ring inset-ring-pink-400/20',
        };
    }
}
