<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Service\User\UserRolesInterface;

final readonly class UserRoleResolver implements UserRolesInterface
{
    public function __construct(private RoleHierarchyInterface $hierarchy)
    {
    }

    /** @param list<string> $roles */
    public function resolveMain(array $roles): ?UserRolesEnum
    {
        $reachable = $this->hierarchy->getReachableRoleNames($roles);

        foreach (UserRolesEnum::priority() as $role) {
            if (in_array($role->value, $reachable, true)) {
                return $role;
            }
        }

        return null;
    }
}

