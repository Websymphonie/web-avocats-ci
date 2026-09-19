<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Constant;

use Exception;
use InvalidArgumentException;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

class ConstantService
{
    public const array MONTHS_FRENCH = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    /**
     * Retourne les mois avec leur numéro.
     */
    /** @return array<string, int> */
    public static function getMonths(): array
    {
        $output = [];
        foreach (self::MONTHS_FRENCH as $k => $v) {
            $output[$v] = $k;
        }
        return $output;
    }

    /**
     * @throws Exception
     */
    public static function getNotifTypeBadge(string $value): string
    {
        return NotificationTypeEnum::getBadgeClass($value);
    }

    /**
     * @throws Exception
     */
    public static function getNotifAccessBadge(string $value): string
    {
        return NotificationTypeEnum::getBadgeClass($value);
    }

    public static function getNotifActionBadge(string $value): string
    {
        $actionEnum = NotificationActionEnum::tryFrom($value);

        if (!$actionEnum) {
            throw new InvalidArgumentException("Action notification invalide : $value");
        }

        return match ($actionEnum) {
            NotificationActionEnum::NOTIF_ADD => ColorEnum::SUCCESS->value,
            NotificationActionEnum::NOTIF_UPDATE => ColorEnum::WARNING->value,
            NotificationActionEnum::NOTIF_DELETE => ColorEnum::DANGER->value,
        };
    }

    public static function getNotifActionValue(string $value): string
    {
        $actionEnum = NotificationActionEnum::tryFrom($value);
        if (!$actionEnum) {
            throw new InvalidArgumentException("Action invalide : $value");
        }

        return match ($actionEnum) {
            NotificationActionEnum::NOTIF_ADD => 'Création',
            NotificationActionEnum::NOTIF_UPDATE => 'Modification',
            NotificationActionEnum::NOTIF_DELETE => 'Suppression',
        };
    }

    /**
     * @throws Exception
     */
    public static function getNotifTypeValue(string $value): string
    {
        return NotificationTypeEnum::getText($value);
    }

    /** @return array<string, string> */
    public static function getActionNotifChoices(): array
    {
        return [
            'Création' => NotificationActionEnum::NOTIF_ADD->value,
            'Modification' => NotificationActionEnum::NOTIF_UPDATE->value,
            'Suppression' => NotificationActionEnum::NOTIF_DELETE->value,
        ];
    }

    /** @return array<string, string> */
    public static function getTypeNotifChoices(): array
    {
        return [
            'Mot de passe' => NotificationTypeEnum::NOTIF_PASSWORD->value,
            'Compte utilisateur' => NotificationTypeEnum::NOTIF_ACCOUNT->value,
        ];
    }
}
