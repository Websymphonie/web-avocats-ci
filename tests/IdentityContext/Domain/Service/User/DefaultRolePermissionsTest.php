<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Domain\Service\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;

final class DefaultRolePermissionsTest extends TestCase
{
    public function testManagerReceivesTheExistingGenericManagementPermissions(): void
    {
        $permissions = DefaultRolePermissions::forRole(UserRolesEnum::ADMIN);

        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
            PermissionEnum::DELETE,
        ], $permissions);
    }

    public function testConfigurableRolesReceiveTheirExistingGenericPermissions(): void
    {
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
            PermissionEnum::DELETE,
        ], DefaultRolePermissions::forRole(UserRolesEnum::ADMIN));
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::PRINT,
        ], DefaultRolePermissions::forRole(UserRolesEnum::AVOCAT));
        self::assertSame([
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
        ], DefaultRolePermissions::forRole(UserRolesEnum::USER));
    }

    public function testAvocatHasTheExistingGenericManagementPermissions(): void
    {
        $avocat = DefaultRolePermissions::forRole(UserRolesEnum::AVOCAT);
        foreach ([PermissionEnum::LIST, PermissionEnum::VIEW, PermissionEnum::CREATE, PermissionEnum::EDIT, PermissionEnum::PRINT] as $permission) {
            self::assertContains($permission, $avocat);
        }
    }

    /**
     * @dataProvider defaultPermissionMatrix
     * @param list<UserRolesEnum> $allowedRoles
     */
    public function testCurrentDefaultsMatchThePermissionMatrix(PermissionEnum $permission, array $allowedRoles): void
    {
        foreach (UserRolesEnum::configurableRoles() as $role) {
            $granted = in_array($permission, DefaultRolePermissions::forRole($role), true);
            self::assertSame(in_array($role, $allowedRoles, true), $granted, $role->value . ' / ' . $permission->value);
        }
    }

    /**
     * @return iterable<string, array{PermissionEnum, list<UserRolesEnum>}>
     */
    public static function defaultPermissionMatrix(): iterable
    {
        yield 'list' => [PermissionEnum::LIST, UserRolesEnum::configurableRoles()];
        yield 'view' => [PermissionEnum::VIEW, UserRolesEnum::configurableRoles()];
        yield 'create' => [PermissionEnum::CREATE, UserRolesEnum::configurableRoles()];
        yield 'edit' => [PermissionEnum::EDIT, UserRolesEnum::configurableRoles()];
        yield 'print' => [PermissionEnum::PRINT, [UserRolesEnum::ADMIN, UserRolesEnum::AVOCAT]];
        yield 'delete' => [PermissionEnum::DELETE, [UserRolesEnum::ADMIN]];
        yield 'role management' => [PermissionEnum::ROLE_MANAGE, []];
    }

    public function testSuperAdministratorHasEveryKnownPermission(): void
    {
        self::assertSame(PermissionEnum::cases(), DefaultRolePermissions::forRole(UserRolesEnum::SUPER_ADMIN));
    }
}
