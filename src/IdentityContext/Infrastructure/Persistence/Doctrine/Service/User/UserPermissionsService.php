<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;
use Websymphonie\IdentityContext\Domain\Service\User\PermissionsInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final readonly class UserPermissionsService implements PermissionsInterface
{
    public function __construct(
        private UserRoleResolver $resolver,
        private RolePermissionsRepositoryInterface $repository,
        private CacheServiceInterface $cacheService,
    ) {}

    public function has(User $user, PermissionEnum $permission): bool
    {
        $role = $this->resolver->resolveMain($user->getRoles());

        if (!$role) {
            return false;
        }

        return in_array($permission, $this->permissionsForRole($role), true);
    }

    /** @return list<PermissionEnum> */
    public function permissions(User $user): array
    {
        $role = $this->resolver->resolveMain($user->getRoles());

        return $role === null ? [] : $this->permissionsForRole($role);
    }

    /** @return list<PermissionEnum> */
    private function permissionsForRole(UserRolesEnum $role): array
    {
        if ($role === UserRolesEnum::SUPER_ADMIN) {
            return DefaultRolePermissions::forRole($role);
        }

        return $this->cacheService->getCache(
            CacheEnum::CACHE_ROLE_PERMISSIONS->withString($role->value),
            fn(): array => $this->repository->getForRole($role)?->getPermissions()
                ?? DefaultRolePermissions::forRole($role),
            [CacheEnum::TAG_ROLE_PERMISSIONS->value],
        );
    }
}
