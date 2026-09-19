<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Service\Password;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Websymphonie\AuthContext\Domain\Exception\InvalidCurrentPasswordException;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;

final readonly class PasswordChange
{
    public function __construct(
        private UserPasswordHasherInterface  $hasher,
        private UserModelRepositoryInterface $repository,
        private RememberMeTokenRevoker       $rememberMeTokenRevoker,
    )
    {
    }

    public function changePassword(User $user, string $newPassword): User
    {
        $passwordHash = $this->hasher->hashPassword($user, $newPassword);
        $identifier = $user->getUserIdentifier();
        $user->setPassword($passwordHash);
        $user->incrementSecurityVersion();
        // Revoke first: if persistence fails, the safer state is a forced re-login.
        $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($identifier);

        return $this->repository->update($user);
    }

    public function validateCurrentPassword(User $user, string $currentPassword): void
    {
        if (!$this->hasher->isPasswordValid($user, $currentPassword)) {
            throw new InvalidCurrentPasswordException();
        }
    }
}
