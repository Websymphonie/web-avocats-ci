<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\QueryHandler\Role;

use Websymphonie\IdentityContext\Application\Usecase\Query\Role\GetRolePermissionsQuery;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Domain\Repository\Role\RolePermissionsRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\DefaultRolePermissions;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetRolePermissionsHandler implements QueryHandler
{
    public function __construct(private RolePermissionsRepositoryInterface $repository)
    {
    }

    public function __invoke(GetRolePermissionsQuery $query): RolePermissions
    {
        return $this->repository->getForRole($query->role)
            ?? new RolePermissions(
                id: null,
                role: $query->role,
                permissions: DefaultRolePermissions::forRole($query->role),
            );
    }
}
