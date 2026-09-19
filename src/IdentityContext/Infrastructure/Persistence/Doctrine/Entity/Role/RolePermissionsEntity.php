<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Role;

use Doctrine\ORM\Mapping as ORM;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Role\RolePermissionsRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\DatesTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;

#[ORM\Entity(repositoryClass: RolePermissionsRepository::class)]
#[ORM\Table(name: 'role_permission_configurations')]
#[ORM\HasLifecycleCallbacks]
class RolePermissionsEntity
{
    use IdTrait;
    use DatesTrait;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $permissions = [];

    public function __construct(
        #[ORM\Column(length: 50, unique: true)]
        private string $role,
    )
    {
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /** @return list<string> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /** @param list<string> $permissions */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}
