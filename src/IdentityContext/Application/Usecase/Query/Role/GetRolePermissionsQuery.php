<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Query\Role;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final readonly class GetRolePermissionsQuery
{
    public function __construct(public UserRolesEnum $role)
    {
    }
}
