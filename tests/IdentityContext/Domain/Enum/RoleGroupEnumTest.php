<?php
declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Domain\Enum;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class RoleGroupEnumTest extends TestCase
{
    public function testAllIncludesEachKleBusinessRole(): void
    {
        $roles = RoleGroupEnum::ALL->roles();

        foreach ([
                     UserRolesEnum::ADMIN,
                     UserRolesEnum::AVOCAT,
                     UserRolesEnum::USER,
                 ] as $role) {
            self::assertContains($role->value, $roles);
        }
    }

    public function testUserAccountGroupIncludesOnlyTheRelevantKleProfiles(): void
    {
        $roles = RoleGroupEnum::USER_ACCOUNT->roles();

        self::assertContains(UserRolesEnum::ADMIN->value, $roles);
        self::assertContains(UserRolesEnum::AVOCAT->value, $roles);
        self::assertNotContains(UserRolesEnum::USER->value, $roles);
    }

    public function testRecentBusinessPermissionsMatchTheExactRoleMatrix(RoleGroupEnum $group, array $expectedRoles): void
    {
        $actualRoles = $group->roles();
        sort($actualRoles);
        sort($expectedRoles);

        self::assertSame($expectedRoles, $actualRoles);
    }
}
