<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Enum\Notification;

use InvalidArgumentException;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum NotificationActionEnum: string
{
    case NOTIF_ADD = 'add';
    case NOTIF_UPDATE = 'update';
    case NOTIF_DELETE = 'delete';

    public const string LABEL_ADD = 'Création';
    public const string LABEL_UPDATE = 'Modification';
    public const string LABEL_DELETE = 'Suppression';

    public static function getValue(string $value): NotificationActionEnum
    {
        return self::tryFrom($value) ?? throw new InvalidArgumentException(sprintf('Unknown notification action "%s".', $value));
    }

    public static function getText(string $value): string
    {
        return match ($value) {
            self::NOTIF_ADD->value => self::LABEL_ADD,
            self::NOTIF_UPDATE->value => self::LABEL_UPDATE,
            self::NOTIF_DELETE->value => self::LABEL_DELETE,
            default => throw new InvalidArgumentException(sprintf('Unknown notification action "%s".', $value)),
        };
    }

    public static function getBadge(string $value): string
    {
        return match ($value) {
            self::NOTIF_ADD->value => ColorEnum::SUCCESS->value,
            self::NOTIF_UPDATE->value => ColorEnum::WARNING->value,
            self::NOTIF_DELETE->value => ColorEnum::DANGER->value,
            default => throw new InvalidArgumentException(sprintf('Unknown notification action "%s".', $value)),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NOTIF_ADD => self::LABEL_ADD,
            self::NOTIF_UPDATE => self::LABEL_UPDATE,
            self::NOTIF_DELETE => self::LABEL_DELETE,
        };
    }
}
