<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Enum\Notification;

use Exception;
use InvalidArgumentException;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum NotificationAccessEnum: string
{
    case NOTIF_PUBLIC = 'public';
    case NOTIF_PRIVATE = 'private';

    public const string LABEL_PUBLIC = 'Public';
    public const string LABEL_PRIVATE = 'Privé';

    public static function getValue(string $value): NotificationAccessEnum
    {
        return match ($value) {
            self::NOTIF_PUBLIC->value => self::NOTIF_PUBLIC,
            self::NOTIF_PRIVATE->value => self::NOTIF_PRIVATE,
            default => null,
        };
    }

    /**
     * @throws Exception
     */
    public static function getText(string $value): string
    {
        return match ($value) {
            self::NOTIF_PUBLIC->value => self::LABEL_PUBLIC,
            self::NOTIF_PRIVATE->value => self::LABEL_PRIVATE,
            default => throw new Exception('Unexpected match value'),
        };
    }

    public static function getBadgeClass(string $value): string
    {
        return match ($value) {
            self::NOTIF_PUBLIC->value => ColorEnum::SUCCESS->value,
            self::NOTIF_PRIVATE->value => ColorEnum::DANGER->value,
            default => throw new InvalidArgumentException("Valeur inconnue : $value"),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NOTIF_PUBLIC => self::LABEL_PUBLIC,
            self::NOTIF_PRIVATE => self::LABEL_PRIVATE,
        };
    }
}
