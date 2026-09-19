<?php

declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User as AppUser;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // L’utilisateur n’est pas activé par l’administrateur.
        if (!$user->getEnabled()) {
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas actif");
        }
    }

    /**
     * @param UserInterface $user
     * @param TokenInterface|null $token
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof AppUser) {
            return;
        }
        if (!$user->getEnabled()) {
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas actif");
        }
    }
}
