<?php

declare(strict_types=1);

namespace Websymphonie\Tests\SharedContext\Infrastructure\Security\Voters;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Websymphonie\IdentityContext\Domain\Enum\RoleGroupEnum;
use Websymphonie\IdentityContext\Domain\Service\User\PermissionsInterface;
use Websymphonie\SharedContext\Infrastructure\Security\Voters\RoleGroupAccessVoter;

final class RoleGroupAccessVoterTest extends TestCase
{
    public function testSuperAdminCanAccessAdminGroupThroughRoleHierarchy(): void
    {
        $permissions = $this->createStub(PermissionsInterface::class);
        $roleHierarchy = $this->createMock(RoleHierarchyInterface::class);
        $roleHierarchy
            ->expects(self::once())
            ->method('getReachableRoleNames')
            ->with(['ROLE_SUPER_ADMIN'])
            ->willReturn(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createStub(UserInterface::class));
        $token->method('getRoleNames')->willReturn(['ROLE_SUPER_ADMIN']);

        $voter = new RoleGroupAccessVoter($permissions, $roleHierarchy);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, RoleGroupEnum::ADMIN, [RoleGroupAccessVoter::ROLE_GROUP_ACCESS]),
        );
    }
}
