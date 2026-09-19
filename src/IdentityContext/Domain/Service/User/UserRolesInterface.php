<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

interface UserRolesInterface
{
    /** @param list<string> $roles */
    public function resolveMain(array $roles): ?UserRolesEnum;
}
