<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Security\Access;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class AccessGroupConstants
{
    public const array ALL_ROLES_ACCESS = [
        UserRolesEnum::SUPER_ADMIN->value,
        UserRolesEnum::ADMIN->value,
        UserRolesEnum::AVOCAT->value,
        UserRolesEnum::USER->value,
    ];
}
