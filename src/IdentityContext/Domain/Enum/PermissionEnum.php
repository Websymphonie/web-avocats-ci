<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Enum;

use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum PermissionEnum: string
{
    case LIST = 'ROLE_LIST';
    case VIEW = 'ROLE_VIEW';
    case CREATE = 'ROLE_CREATE';
    case EDIT = 'ROLE_EDIT';
    case DELETE = 'ROLE_DELETE';
    case PRINT = 'ROLE_PRINT';
    case ROLE_MANAGE = 'ROLE_MANAGE';

    // Permissions métier configurables

    public static function configurableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn(self $permission): bool => $permission !== self::ROLE_MANAGE,
        ));
    }

    public function label(): string
    {
        return match ($this) {
            self::LIST => 'Lecture (liste)',
            self::VIEW => 'Lecture (détail)',
            self::CREATE => 'Création',
            self::EDIT => 'Édition',
            self::DELETE => 'Suppression',
            self::PRINT => 'Impression',
            self::ROLE_MANAGE => 'Rôles : configuration de la matrice des permissions',
        };
    }

    public function badge(): ColorEnum
    {
        return match ($this) {
            self::DELETE => ColorEnum::DANGER,
            self::CREATE => ColorEnum::SUCCESS,
            self::EDIT => ColorEnum::WARNING,
            self::LIST => ColorEnum::PRIMARY,
            self::VIEW => ColorEnum::INFO,
            self::PRINT => ColorEnum::SECONDARY,
            self::ROLE_MANAGE => ColorEnum::PRIMARY,
        };
    }
}
