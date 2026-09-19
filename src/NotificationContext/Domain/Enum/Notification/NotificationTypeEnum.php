<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\Enum\Notification;

use Exception;
use InvalidArgumentException;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum NotificationTypeEnum: string
{
    case NOTIF_PASSWORD = 'password';
    case NOTIF_ACCOUNT = 'account';
    case NOTIF_INFO = 'infos';

    public const string LABEL_PASSWORD = 'Mot de passe';
    public const string LABEL_ACCOUNT = 'Compte utilisateur';
    public const string LABEL_INFO = 'Information';

    public static function getValue(string $value): NotificationTypeEnum
    {
        return match ($value) {
            self::NOTIF_PASSWORD->value => self::NOTIF_PASSWORD,
            self::NOTIF_ACCOUNT->value => self::NOTIF_ACCOUNT,
            self::NOTIF_INFO->value => self::NOTIF_INFO,
            default => null,
        };
    }

    /**
     * @throws Exception
     */
    public static function getText(string $value): string
    {
        return match ($value) {
            self::NOTIF_PASSWORD->value => self::LABEL_PASSWORD,
            self::NOTIF_ACCOUNT->value => self::LABEL_ACCOUNT,
            self::NOTIF_INFO->value => self::LABEL_INFO,
            default => throw new Exception('Unexpected match value'),
        };
    }

    public static function getBadgeClass(string $value): string
    {
        return match ($value) {
            self::NOTIF_ACCOUNT->value => ColorEnum::SUCCESS->value,
            self::NOTIF_PASSWORD->value => ColorEnum::WARNING->value,
            self::NOTIF_INFO->value => ColorEnum::INFO->value,
            default => throw new InvalidArgumentException("Valeur inconnue : $value"),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NOTIF_PASSWORD => self::LABEL_PASSWORD,
            self::NOTIF_ACCOUNT => self::LABEL_ACCOUNT,
            self::NOTIF_INFO => self::LABEL_INFO,
        };
    }
}
