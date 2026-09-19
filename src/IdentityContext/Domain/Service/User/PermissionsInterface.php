<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface PermissionsInterface
{
    public function has(User $user, PermissionEnum $permission): bool;

    /** @return list<PermissionEnum> */
    public function permissions(User $user): array;
}
