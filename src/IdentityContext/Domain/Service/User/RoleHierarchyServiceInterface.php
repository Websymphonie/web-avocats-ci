<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

interface RoleHierarchyServiceInterface
{
    /**
     * @param list<string> $roles
     * @return list<string>
     */
    public function getReachableRoles(array $roles): array;
}
