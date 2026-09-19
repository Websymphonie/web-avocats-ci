<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Usecase\CommandHandler\Role;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Usecase\Command\Role\UpdateRolePermissionsCommand;
use Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Role\UpdateRolePermissionsHandler;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final class UpdateRolePermissionsHandlerTest extends TestCase
{
    public function testItPersistsOnlyConfigurableAndUniquePermissions(): void
    {
        $current = new RolePermissions(7, UserRolesEnum::ADMIN, [PermissionEnum::VIEW]);
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('getForRole')
            ->with(UserRolesEnum::ADMIN)
            ->willReturn($current);
        $repository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (RolePermissions $configuration): RolePermissions {
                self::assertSame(7, $configuration->getId());
                self::assertSame(UserRolesEnum::ADMIN, $configuration->getRole());
                self::assertSame([
                    PermissionEnum::VIEW,
                    PermissionEnum::CREATE,
                ], $configuration->getPermissions());

                return $configuration;
            });
        $cacheService = $this->createMock(CacheServiceInterface::class);
        $cacheService
            ->expects(self::once())
            ->method('invalidateTag')
            ->with(CacheEnum::TAG_ROLE_PERMISSIONS->value);

        $configuration = (new UpdateRolePermissionsHandler($repository, $cacheService))(new UpdateRolePermissionsCommand(
            UserRolesEnum::ADMIN,
            [
                PermissionEnum::VIEW->value,
                PermissionEnum::CREATE->value,
                PermissionEnum::VIEW->value,
                PermissionEnum::ROLE_MANAGE->value,
                'UNKNOWN_PERMISSION',
            ],
        ));

        self::assertSame(7, $configuration->getId());
    }

    public function testItRefusesToConfigureTheSuperAdministrator(): void
    {
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository->expects(self::never())->method('getForRole');
        $repository->expects(self::never())->method('save');
        $cacheService = $this->createMock(CacheServiceInterface::class);
        $cacheService->expects(self::never())->method('invalidateTag');

        $this->expectException(InvalidArgumentException::class);

        (new UpdateRolePermissionsHandler($repository, $cacheService))(new UpdateRolePermissionsCommand(UserRolesEnum::SUPER_ADMIN));
    }
}
