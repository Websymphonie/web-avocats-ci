<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Usecase\QueryHandler\Role;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Usecase\Query\Role\ListRolePermissionsQuery;
use Websymphonie\IdentityContext\Application\Usecase\QueryHandler\Role\ListRolePermissionsHandler;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;

final class ListRolePermissionsHandlerTest extends TestCase
{
    public function testItKeepsPersistedConfigurationAndUsesDefaultsOnlyForMissingRoles(): void
    {
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('list')
            ->willReturn([
                new RolePermissions(9, UserRolesEnum::AVOCAT, [PermissionEnum::CREATE]),
            ]);

        $configurations = (new ListRolePermissionsHandler($repository))(new ListRolePermissionsQuery());

        self::assertCount(3, $configurations);
        self::assertSame(UserRolesEnum::ADMIN, $configurations[0]->getRole());
        self::assertSame(
            DefaultRolePermissions::forRole(UserRolesEnum::ADMIN),
            $configurations[0]->getPermissions(),
        );
        self::assertSame(UserRolesEnum::AVOCAT, $configurations[1]->getRole());
        self::assertSame([PermissionEnum::CREATE], $configurations[1]->getPermissions());
        self::assertSame(UserRolesEnum::USER, $configurations[2]->getRole());
        self::assertSame(
            DefaultRolePermissions::forRole(UserRolesEnum::USER),
            $configurations[2]->getPermissions(),
        );
    }
}
