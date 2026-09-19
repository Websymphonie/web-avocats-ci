<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Model\Role;

use DateTimeImmutable;
use InvalidArgumentException;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class RolePermissions
{
    /**
     * @param list<PermissionEnum> $permissions
     */
    public function __construct(
        private ?int $id,
        private UserRolesEnum $role,
        private array $permissions,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
    ) {
        // Legacy roles remain representable so historical persisted rows can
        // be read safely, but they are no longer returned by the role catalog
        // and cannot be updated by the command handler.
        if ($role === UserRolesEnum::SUPER_ADMIN) {
            throw new InvalidArgumentException(sprintf('Le rôle "%s" ne peut pas être configuré.', $role->value));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): UserRolesEnum
    {
        return $this->role;
    }

    /** @return list<PermissionEnum> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /** @return list<string> */
    public function getPermissionCodes(): array
    {
        return array_map(static fn(PermissionEnum $permission): string => $permission->value, $this->permissions);
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
