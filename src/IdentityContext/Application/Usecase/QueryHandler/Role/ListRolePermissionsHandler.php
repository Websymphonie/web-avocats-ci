<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\QueryHandler\Role;

use Websymphonie\IdentityContext\Application\Usecase\Query\Role\ListRolePermissionsQuery;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class ListRolePermissionsHandler implements QueryHandler
{
    public function __construct(private RolePermissionsRepositoryInterface $repository)
    {
    }

    /** @return list<RolePermissions> */
    public function __invoke(ListRolePermissionsQuery $query): array
    {
        $configuredRoles = [];
        foreach ($this->repository->list() as $rolePermissions) {
            $configuredRoles[$rolePermissions->getRole()->value] = $rolePermissions;
        }

        return array_map(
            static fn(UserRolesEnum $role): RolePermissions => $configuredRoles[$role->value]
                ?? new RolePermissions(null, $role, DefaultRolePermissions::forRole($role)),
            UserRolesEnum::configurableRoles(),
        );
    }
}
