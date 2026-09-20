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

    /**
     * @dataProvider roleMatrix
     * @param list<string> $expectedRoles
     */
    public function testRecentBusinessPermissionsMatchTheExactRoleMatrix(RoleGroupEnum $group, array $expectedRoles): void
    {
        $actualRoles = $group->roles();
        sort($actualRoles);
        sort($expectedRoles);

        self::assertSame($expectedRoles, $actualRoles);
    }

    /**
     * @return iterable<string, array{RoleGroupEnum, list<string>}>
     */
    public static function roleMatrix(): iterable
    {
        yield 'super' => [RoleGroupEnum::SUPER, [UserRolesEnum::SUPER_ADMIN->value]];
        yield 'user account' => [RoleGroupEnum::USER_ACCOUNT, [
            UserRolesEnum::SUPER_ADMIN->value,
            UserRolesEnum::ADMIN->value,
            UserRolesEnum::AVOCAT->value,
        ]];
        yield 'logs' => [RoleGroupEnum::LOGS, [UserRolesEnum::SUPER_ADMIN->value]];
        yield 'images' => [RoleGroupEnum::IMAGES, [UserRolesEnum::SUPER_ADMIN->value]];
        yield 'reglages' => [RoleGroupEnum::REGLAGES, [
            UserRolesEnum::SUPER_ADMIN->value,
            UserRolesEnum::ADMIN->value,
        ]];
        yield 'maintenance' => [RoleGroupEnum::MAINTENANCE, [UserRolesEnum::SUPER_ADMIN->value]];
        foreach ([
            'news' => RoleGroupEnum::NEWS,
            'news categories' => RoleGroupEnum::CATEGORY_NEWS,
            'events' => RoleGroupEnum::EVENTS,
            'event categories' => RoleGroupEnum::CATEGORY_EVENTS,
            'videos' => RoleGroupEnum::VIDEOS,
            'galleries' => RoleGroupEnum::GALLERIES,
            'tags' => RoleGroupEnum::TAGS,
            'documents' => RoleGroupEnum::DOCUMENTS,
            'trainings' => RoleGroupEnum::TRAININGS,
            'course modules' => RoleGroupEnum::COURSE_MODULES,
            'enrollments' => RoleGroupEnum::ENROLLMENTS,
            'training categories' => RoleGroupEnum::CATEGORY_TRAININGS,
            'training tags' => RoleGroupEnum::TAG_TRAININGS,
            'payments' => RoleGroupEnum::PAYMENTS,
            'payment offers' => RoleGroupEnum::PAYMENT_OFFERS,
        ] as $label => $group) {
            yield $label => [$group, [
                UserRolesEnum::SUPER_ADMIN->value,
                UserRolesEnum::ADMIN->value,
            ]];
        }
        yield 'all' => [RoleGroupEnum::ALL, [
            UserRolesEnum::SUPER_ADMIN->value,
            UserRolesEnum::ADMIN->value,
            UserRolesEnum::AVOCAT->value,
            UserRolesEnum::USER->value,
        ]];
    }
}
