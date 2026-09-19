<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

/**
 * Initial permissions used only when a role has no persisted configuration.
 */
final class DefaultRolePermissions
{
    /** @return list<PermissionEnum> */
    public static function forRole(UserRolesEnum $role): array
    {
        if ($role === UserRolesEnum::SUPER_ADMIN) {
            return PermissionEnum::cases();
        }

        $permissions = match ($role) {
            UserRolesEnum::ADMIN => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
                PermissionEnum::PRINT,
                PermissionEnum::DELETE,
            ],
            UserRolesEnum::AVOCAT => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
                PermissionEnum::PRINT,
            ],
            default => [
                PermissionEnum::LIST,
                PermissionEnum::VIEW,
                PermissionEnum::CREATE,
                PermissionEnum::EDIT,
            ],
        };

        foreach (self::businessDefaults() as $permission => $roles) {
            if (in_array($role, $roles, true)) {
                $permissions[] = PermissionEnum::from($permission);
            }
        }

        return $permissions;
    }

    /** @return array<string, list<UserRolesEnum>> */
    private static function businessDefaults(): array
    {
        return [

        ];
    }
}
