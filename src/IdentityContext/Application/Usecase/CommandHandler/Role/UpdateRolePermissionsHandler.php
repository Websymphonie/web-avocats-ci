<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Role;

use InvalidArgumentException;
use Websymphonie\IdentityContext\Application\Usecase\Command\Role\UpdateRolePermissionsCommand;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;

final readonly class UpdateRolePermissionsHandler implements CommandHandler
{
    public function __construct(
        private RolePermissionsRepositoryInterface $repository,
        private CacheServiceInterface $cacheService,
    ) {}

    public function __invoke(UpdateRolePermissionsCommand $command): RolePermissions
    {
        if (!in_array($command->role, UserRolesEnum::configurableRoles(), true)) {
            throw new InvalidArgumentException('Les permissions du super administrateur ne peuvent pas être modifiées.');
        }

        $current = $this->repository->getForRole($command->role);

        $configuration = $this->repository->save(new RolePermissions(
            id: $current?->getId(),
            role: $command->role,
            permissions: $this->resolvePermissions($command->permissions),
            createdAt: $current?->getCreatedAt(),
            updatedAt: $current?->getUpdatedAt(),
        ));

        $this->cacheService->invalidateTag(CacheEnum::TAG_ROLE_PERMISSIONS->value);

        return $configuration;
    }

    /**
     * @param list<mixed> $permissionCodes
     * @return list<PermissionEnum>
     */
    private function resolvePermissions(array $permissionCodes): array
    {
        $allowedCodes = array_map(
            static fn(PermissionEnum $permission): string => $permission->value,
            PermissionEnum::configurableCases(),
        );
        $permissions = [];

        foreach ($permissionCodes as $permissionCode) {
            if (!is_string($permissionCode) || !in_array($permissionCode, $allowedCodes, true)) {
                continue;
            }

            $permission = PermissionEnum::from($permissionCode);
            if (!in_array($permission, $permissions, true)) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }
}
