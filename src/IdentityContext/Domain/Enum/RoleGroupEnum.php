<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Enum;

enum RoleGroupEnum: string
{
    case SUPER = 'SUPER';
    case ADMIN = 'ADMIN';
    case AVOCAT = 'AVOCAT';
    case USER = 'USER';

    // Fonctionnalités historiques. Pour les codes métier configurables,
    // l'autorisation finale est désormais résolue par PermissionEnum.
    case LOGS = 'LOGS';
    case USER_ACCOUNT = 'USER_ACCOUNT';
    case IMAGES = 'IMAGES';
    case REGLAGES = 'REGLAGES';
    case MAINTENANCE = 'MAINTENANCE';

    case ALL = 'ALL';

    /**
     * Résolution finale
     */
    /** @return list<string> */
    public function roles(): array
    {
        $roles = $this->directRoles();

        foreach ($this->children() as $child) {
            $roles = array_merge($roles, $child->roles());
        }

        return array_values(array_unique($roles));
    }

    /**
     * Groupes atomiques → rôles directs
     */
    /** @return list<string> */
    private function directRoles(): array
    {
        return match ($this) {
            self::SUPER => [UserRolesEnum::SUPER_ADMIN->value],
            self::ADMIN => [UserRolesEnum::ADMIN->value],
            self::AVOCAT => [UserRolesEnum::AVOCAT->value],
            self::USER => [UserRolesEnum::USER->value],
            default => [],
        };
    }

    /**
     * Groupes composites → sous-groupes
     */
    /** @return list<self> */
    private function children(): array
    {
        return match ($this) {
            self::LOGS => [self::SUPER],
            self::USER_ACCOUNT => [self::SUPER, self::ADMIN, self::AVOCAT],
            self::IMAGES => [self::SUPER],
            self::REGLAGES => [self::SUPER, self::ADMIN],
            self::MAINTENANCE => [self::SUPER],

            self::ALL => [
                self::SUPER,
                self::ADMIN,
                self::AVOCAT,
                self::USER,
            ],

            default => [],
        };
    }
}
