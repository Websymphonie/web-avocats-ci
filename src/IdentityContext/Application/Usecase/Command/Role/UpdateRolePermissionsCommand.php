<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\Command\Role;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class UpdateRolePermissionsCommand
{
    /** @param list<string> $permissions */
    public function __construct(
        public UserRolesEnum $role,
        public array $permissions = [],
    ) {
    }
}
