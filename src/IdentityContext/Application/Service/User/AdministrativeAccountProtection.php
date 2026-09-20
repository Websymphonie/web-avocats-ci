<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\User;

use Websymphonie\IdentityContext\Domain\Enum\UserRolesEnum;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Domain\Exception\AccessDeniedException;

/**
 * Centralizes the invariants protecting administrative accounts.
 *
 * This is an account-safety policy, not a replacement for the RBAC voters.
 */
final readonly class AdministrativeAccountProtection
{
    public function __construct(
        private CurrentUserProvider $currentUserProvider,
        private UserModelRepositoryInterface $repository,
    ) {
    }

    /** @param list<string>|null $roles */
    public function assertCanCreateWithRoles(?array $roles): void
    {
        $this->assertKnownRoles($roles);

        if (in_array(UserRolesEnum::SUPER_ADMIN->value, $roles ?? [], true)) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }
    }

    /**
     * @param list<string>|null $roles
     */
    public function assertCanUpdate(User $target, ?array $roles, bool $enabled): void
    {
        $requestedRoles = $roles ?? [];
        $this->assertKnownRoles($requestedRoles);

        $targetIsSuperAdmin = $this->hasRole($target, UserRolesEnum::SUPER_ADMIN->value);
        $actorIsSuperAdmin = $this->currentActorIsSuperAdmin();

        if (!$targetIsSuperAdmin && in_array(UserRolesEnum::SUPER_ADMIN->value, $requestedRoles, true)) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }

        if ($targetIsSuperAdmin && !$actorIsSuperAdmin) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }

        if ($targetIsSuperAdmin && $target->getEnabled() === true && !$enabled) {
            $this->assertNotLastActiveSuperAdmin();
        }

        if ($targetIsSuperAdmin
            && !in_array(UserRolesEnum::SUPER_ADMIN->value, $requestedRoles, true)
        ) {
            $this->assertNotLastActiveSuperAdmin();
        }
    }

    public function assertCanDelete(User $target): void
    {
        if (!$this->hasRole($target, UserRolesEnum::SUPER_ADMIN->value)) {
            return;
        }

        if (!$this->currentActorIsSuperAdmin()) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }

        if ($target->getEnabled() === true) {
            $this->assertNotLastActiveSuperAdmin();
        }
    }

    /** @param list<User> $targets */
    public function assertCanDeleteMany(array $targets): void
    {
        $superAdmins = array_values(array_filter(
            $targets,
            fn(User $target): bool => $this->hasRole($target, UserRolesEnum::SUPER_ADMIN->value),
        ));

        if ($superAdmins === []) {
            return;
        }

        if (!$this->currentActorIsSuperAdmin()) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }

        $activeTargets = count(array_filter(
            $superAdmins,
            static fn(User $target): bool => $target->getEnabled() === true,
        ));

        if ($activeTargets > 0
            && $this->repository->countActiveSuperAdmins() - $activeTargets < 1
        ) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }
    }

    /** @param list<string>|null $roles */
    private function assertKnownRoles(?array $roles): void
    {
        $allowedRoles = array_map(
            static fn(UserRolesEnum $role): string => $role->value,
            UserRolesEnum::allRoles(),
        );

        foreach ($roles ?? [] as $role) {
            if (!in_array($role, $allowedRoles, true)) {
                throw new AccessDeniedException('IDENTITY_ROLES');
            }
        }
    }

    private function assertNotLastActiveSuperAdmin(): void
    {
        if ($this->repository->countActiveSuperAdmins() <= 1) {
            throw new AccessDeniedException('ROLE_SUPER_ADMIN');
        }
    }

    private function currentActorIsSuperAdmin(): bool
    {
        $actor = $this->currentUserProvider->getUser();

        return $actor !== null && $this->hasRole($actor, UserRolesEnum::SUPER_ADMIN->value);
    }

    private function hasRole(User $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }
}
