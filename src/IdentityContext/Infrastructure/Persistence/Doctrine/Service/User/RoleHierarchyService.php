<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Websymphonie\IdentityContext\Domain\Service\User\RoleHierarchyServiceInterface;

readonly class RoleHierarchyService implements RoleHierarchyServiceInterface
{
    public function __construct(private RoleHierarchyInterface $roleHierarchy)
    {
    }

    /**
     * @param list<string> $roles
     * @return list<string>
     */
    public function getReachableRoles(array $roles): array
    {
        return $this->roleHierarchy->getReachableRoleNames($roles);
    }
}
