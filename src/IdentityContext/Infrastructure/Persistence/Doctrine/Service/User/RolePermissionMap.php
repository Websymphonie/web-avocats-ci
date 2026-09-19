<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;

final class RolePermissionMap
{
    /**
     * @return PermissionEnum[]
     */
    public static function forRole(UserRolesEnum $role): array
    {
        return DefaultRolePermissions::forRole($role);
    }
}
