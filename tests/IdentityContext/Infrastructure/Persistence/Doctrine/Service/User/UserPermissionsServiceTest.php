<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User\UserPermissionsService;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User\UserRoleResolver;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final class UserPermissionsServiceTest extends TestCase
{
    public function testItUsesThePersistedPermissionsForAConfigurableRole(): void
    {
        $hierarchy = $this->createMock(RoleHierarchyInterface::class);
        $hierarchy->method('getReachableRoleNames')->willReturn([UserRolesEnum::AVOCAT->value, UserRolesEnum::USER->value]);
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository
            ->expects(self::exactly(2))
            ->method('getForRole')
            ->with(UserRolesEnum::AVOCAT)
            ->willReturn(new RolePermissions(1, UserRolesEnum::AVOCAT, [PermissionEnum::CREATE]));
        $service = new UserPermissionsService(new UserRoleResolver($hierarchy), $repository, $this->cacheService());
        $user = (new User())->setRoles([UserRolesEnum::AVOCAT->value]);

        self::assertTrue($service->has($user, PermissionEnum::CREATE));
        self::assertFalse($service->has($user, PermissionEnum::EDIT));
    }

    public function testAnEmptyPersistedConfigurationDoesNotFallBackToDefaults(): void
    {
        $hierarchy = $this->createMock(RoleHierarchyInterface::class);
        $hierarchy->method('getReachableRoleNames')->willReturn([UserRolesEnum::AVOCAT->value]);
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository
            ->expects(self::exactly(2))
            ->method('getForRole')
            ->with(UserRolesEnum::AVOCAT)
            ->willReturn(new RolePermissions(2, UserRolesEnum::AVOCAT, []));
        $service = new UserPermissionsService(new UserRoleResolver($hierarchy), $repository, $this->cacheService());
        $user = (new User())->setRoles([UserRolesEnum::AVOCAT->value]);

        self::assertFalse($service->has($user, PermissionEnum::LIST));
        self::assertSame([], $service->permissions($user));
    }

    private function cacheService(): CacheServiceInterface
    {
        return new class implements CacheServiceInterface {
            public function getCache(string $key, callable $callback, array $tags = [], ?int $ttl = null): mixed
            {
                return $callback();
            }

            public function deleteCache(string $key): void
            {
            }

            public function clearAllCache(): void
            {
            }

            public function invalidateTag(string $tag): void
            {
            }
        };
    }

    public function testTheSuperAdministratorAlwaysKeepsAllPermissions(): void
    {
        $hierarchy = $this->createMock(RoleHierarchyInterface::class);
        $hierarchy->method('getReachableRoleNames')->willReturn(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);
        $repository = $this->createMock(RolePermissionsRepositoryInterface::class);
        $repository->expects(self::never())->method('getForRole');
        $service = new UserPermissionsService(new UserRoleResolver($hierarchy), $repository, $this->cacheService());
        $user = (new User())->setRoles([UserRolesEnum::SUPER_ADMIN->value]);

        self::assertTrue($service->has($user, PermissionEnum::ROLE_MANAGE));
        self::assertContains(PermissionEnum::ROLE_MANAGE, $service->permissions($user));
    }
}
