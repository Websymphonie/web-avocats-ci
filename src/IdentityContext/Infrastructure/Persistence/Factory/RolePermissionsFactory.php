<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Factory;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Role\RolePermissionsEntity;

final class RolePermissionsFactory
{
    public function fromEntity(RolePermissionsEntity $entity): RolePermissions
    {
        $permissions = [];
        $legacyBusinessConfiguration = in_array($entity->getRole(), [UserRolesEnum::USER->value], true)
            && [] !== array_intersect($entity->getPermissions(), self::legacyMarkers());

        foreach ($entity->getPermissions() as $permissionCode) {
            $permission = PermissionEnum::tryFrom($permissionCode);
            if ($permission !== null
                && $permission !== PermissionEnum::ROLE_MANAGE
                && !($legacyBusinessConfiguration && self::isBusinessPermission($permission))) {
                $permissions[] = $permission;
            }
        }

        return new RolePermissions(
            id: $entity->getId(),
            role: UserRolesEnum::from($entity->getRole()),
            permissions: $permissions,
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    /** @return list<string> */
    private static function legacyMarkers(): array
    {
        return [];
    }

    private static function isBusinessPermission(PermissionEnum $permission): bool
    {
        return !in_array($permission, [
            PermissionEnum::LIST,
            PermissionEnum::VIEW,
            PermissionEnum::CREATE,
            PermissionEnum::EDIT,
            PermissionEnum::DELETE,
            PermissionEnum::PRINT,
        ], true);
    }

    /**
     * @param list<RolePermissionsEntity> $entities
     * @return list<RolePermissions>
     */
    public function fromEntityList(array $entities): array
    {
        return array_map($this->fromEntity(...), $entities);
    }

    public function toNewEntity(RolePermissions $rolePermissions): RolePermissionsEntity
    {
        $entity = new RolePermissionsEntity($rolePermissions->getRole()->value);
        $this->updateEntity($entity, $rolePermissions);

        return $entity;
    }

    public function updateEntity(RolePermissionsEntity $entity, RolePermissions $rolePermissions): void
    {
        $entity->setPermissions($rolePermissions->getPermissionCodes());
    }
}
