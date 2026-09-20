<?php

declare(strict_types=1);

namespace Websymphonie\Tests\IdentityContext\Application\Service\User;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Exception\AccessDeniedException;

final class AdministrativeAccountProtectionTest extends TestCase
{
    public function testStandardCreationCannotGrantSuperAdmin(): void
    {
        $policy = $this->policy(null, 0);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanCreateWithRoles(['ROLE_SUPER_ADMIN']);
    }

    public function testAdminCannotModifySuperAdminTarget(): void
    {
        $policy = $this->policy($this->user(['ROLE_ADMIN']), 1);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanUpdate($this->user(['ROLE_SUPER_ADMIN']), ['ROLE_ADMIN'], true);
    }

    public function testLastActiveSuperAdminCannotBeDemoted(): void
    {
        $policy = $this->policy($this->user(['ROLE_SUPER_ADMIN']), 1);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanUpdate($this->user(['ROLE_SUPER_ADMIN']), ['ROLE_ADMIN'], true);
    }

    public function testLastActiveSuperAdminCannotBeDisabled(): void
    {
        $policy = $this->policy($this->user(['ROLE_SUPER_ADMIN']), 1);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanUpdate($this->user(['ROLE_SUPER_ADMIN']), ['ROLE_SUPER_ADMIN'], false);
    }

    public function testLastActiveSuperAdminCannotBeDeleted(): void
    {
        $policy = $this->policy($this->user(['ROLE_SUPER_ADMIN']), 1);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanDelete($this->user(['ROLE_SUPER_ADMIN']));
    }

    public function testBulkDeleteCannotRemoveAllActiveSuperAdmins(): void
    {
        $actor = $this->user(['ROLE_SUPER_ADMIN']);
        $repository = $this->createStub(UserModelRepositoryInterface::class);
        $repository->method('countActiveSuperAdmins')->willReturn(2);
        $currentUserProvider = $this->createStub(CurrentUserProvider::class);
        $currentUserProvider->method('getUser')->willReturn($actor);
        $policy = new AdministrativeAccountProtection($currentUserProvider, $repository);

        $this->expectException(AccessDeniedException::class);
        $policy->assertCanDeleteMany([
            $this->user(['ROLE_SUPER_ADMIN']),
            $this->user(['ROLE_SUPER_ADMIN']),
        ]);
    }

    public function testBulkDeleteRejectsProtectedTargetBeforeAnyRemoval(): void
    {
        $repository = $this->createMock(UserModelRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findByIds')
            ->with([1, 2])
            ->willReturn([$this->user(['ROLE_USER']), $this->user(['ROLE_SUPER_ADMIN'])]);
        $repository->expects(self::never())->method('remove');

        $currentUserProvider = $this->createStub(CurrentUserProvider::class);
        $currentUserProvider->method('getUser')->willReturn($this->user(['ROLE_ADMIN']));
        $policy = new AdministrativeAccountProtection($currentUserProvider, $repository);

        $this->expectException(AccessDeniedException::class);
        (new \Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User\DeleteUsersHandler($repository, $policy))(
            new \Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUsersCommand([1, 2]),
        );
    }

    /** @param list<string> $roles */
    private function user(array $roles): User
    {
        $user = new User();
        $user->setRoles($roles);
        $user->setEnabled(true);

        return $user;
    }

    /** @param list<string>|null $roles */
    private function policy(?User $actor, int $activeSuperAdmins): AdministrativeAccountProtection
    {
        $currentUserProvider = $this->createStub(CurrentUserProvider::class);
        $currentUserProvider->method('getUser')->willReturn($actor);
        $repository = $this->createStub(UserModelRepositoryInterface::class);
        $repository->method('countActiveSuperAdmins')->willReturn($activeSuperAdmins);

        return new AdministrativeAccountProtection($currentUserProvider, $repository);
    }
}
