<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\Password;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\PasswordHashInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

final readonly class PasswordHasher implements PasswordHashInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function hash(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }


    public function hashReset(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }

    public function isValid(User $user, string $plainPassword): bool
    {
        return $this->hasher->isPasswordValid($user, $plainPassword);
    }
}