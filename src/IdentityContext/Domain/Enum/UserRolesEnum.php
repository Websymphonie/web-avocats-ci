<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Enum;

use Exception;
use Websymphonie\IdentityContext\Presenter\Constant\UserRouteConstants;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

enum UserRolesEnum: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case AVOCAT = 'ROLE_AVOCAT';
    case USER = 'ROLE_USER';

    /** @return list<self> */
    public static function priority(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::AVOCAT,
            self::USER,
        ];
    }

    /** @return list<self> */
    public static function allRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::AVOCAT,
            self::USER,
        ];
    }

    /** @return list<self> */
    public static function legacyRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::USER,
        ];
    }

    /**
     * @throws Exception
     */
    /** @return list<string> */
    public static function group(RoleGroupEnum $group): array
    {
        return $group->roles();
    }

    /** @return list<self> */
    public static function roles(): array
    {
        return self::configurableRoles();
    }

    /** @return list<self> */
    public static function configurableRoles(): array
    {
        return [
            self::ADMIN,
            self::AVOCAT,
            self::USER,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::ADMIN => 'Manager',
            self::AVOCAT => 'Avocat(e)',
            self::USER => 'Utilisateur',
        };
    }

    public function badge(): ColorEnum
    {
        return match ($this) {

            // Direction et stratégiques
            self::SUPER_ADMIN => ColorEnum::SUCCESS,

            // Chefs de département
            self::ADMIN => ColorEnum::PRIMARY,

            // Employés de départements
            self::AVOCAT => ColorEnum::PINK,
            self::USER => ColorEnum::PURPLE,
        };
    }

    public function route(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => UserRouteConstants::SUPER_ADMIN_ROUTE,
            self::ADMIN => UserRouteConstants::ADMIN_ROUTE,
            self::AVOCAT => UserRouteConstants::AVOCAT_ROUTE,
            self::USER => UserRouteConstants::USER_ROUTE,
        };
    }
}
