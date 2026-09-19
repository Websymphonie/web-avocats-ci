<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Infrastructure\Persistence\Factory\Role;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Model\Role\RolePermissions;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Factory\RolePermissionsFactory;

final class RolePermissionsFactoryTest extends TestCase
{
    public function testItMapsThePermissionCodesToTheDoctrineEntity(): void
    {
        $configuration = new RolePermissions(null, UserRolesEnum::ADMIN, [
            PermissionEnum::VIEW,
            PermissionEnum::EDIT,
        ]);

        $entity = (new RolePermissionsFactory())->toNewEntity($configuration);

        self::assertSame(UserRolesEnum::ADMIN->value, $entity->getRole());
        self::assertSame([
            PermissionEnum::VIEW->value,
            PermissionEnum::EDIT->value,
        ], $entity->getPermissions());
    }
}
