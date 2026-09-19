<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Repository\Role;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;

interface RolePermissionsRepositoryInterface
{
    public function getForRole(UserRolesEnum $role): ?RolePermissions;

    /** @return list<RolePermissions> */
    public function list(): array;

    public function save(RolePermissions $rolePermissions): RolePermissions;
}
