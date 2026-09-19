<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Domain\Service\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;

final class DefaultRolePermissionsTest extends TestCase
{
    public function testLegacyAdministratorKeepsOnlyGenericReadOnlyPermissions(): void
    {
        $permissions = DefaultRolePermissions::forRole(UserRolesEnum::ADMIN);

        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
        ], $permissions);
    }

    public function testNonAdministrativeAndLegacyRolesKeepGenericReadOnlyPermissions(): void
    {
        foreach ([
                     UserRolesEnum::ADMIN,
                     UserRolesEnum::AVOCAT,
                     UserRolesEnum::USER,
                 ] as $role) {
            $permissions = DefaultRolePermissions::forRole($role);
            self::assertContains(PermissionEnum::LIST, $permissions);
            self::assertContains(PermissionEnum::VIEW, $permissions);
            self::assertNotContains(PermissionEnum::CREATE, $permissions);
            self::assertNotContains(PermissionEnum::EDIT, $permissions);
        }
    }

    public function testAvocatHasTheExistingGenericManagementPermissions(): void
    {
        $avocat = DefaultRolePermissions::forRole(UserRolesEnum::AVOCAT);
        foreach ([PermissionEnum::LIST, PermissionEnum::VIEW, PermissionEnum::CREATE, PermissionEnum::EDIT, PermissionEnum::PRINT] as $permission) {
            self::assertContains($permission, $avocat);
        }
    }

    public function testBusinessDefaultsMatchTheDocumentedMatrix(PermissionEnum $permission, array $allowedRoles): void
    {
        foreach (UserRolesEnum::configurableRoles() as $role) {
            $granted = in_array($permission, DefaultRolePermissions::forRole($role), true);
            self::assertSame(in_array($role, $allowedRoles, true), $granted, $role->value . ' / ' . $permission->value);
        }
    }

    public function testSuperAdministratorHasEveryKnownPermission(): void
    {
        self::assertSame(PermissionEnum::cases(), DefaultRolePermissions::forRole(UserRolesEnum::SUPER_ADMIN));
    }
}
