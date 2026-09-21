<?php

declare(strict_types=1);

namespace WebsymphonieTests\IdentityContext\Domain\Enum;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;

final class UserRolesEnumTest extends TestCase
{
    public function testAvocatRoleUsesMemberAreaRoute(): void
    {
        self::assertSame('app_member', UserRolesEnum::AVOCAT->route());
    }

    public function testAdministrativeRolesUseTheBackofficeDashboardRoute(): void
    {
        self::assertSame('app_admin', UserRolesEnum::SUPER_ADMIN->route());
        self::assertSame('app_admin', UserRolesEnum::ADMIN->route());
    }
}
