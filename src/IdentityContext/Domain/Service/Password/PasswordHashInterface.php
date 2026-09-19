<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\Password;

use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface PasswordHashInterface
{
    public function hash(User $user, string $plainPassword): string;

    public function hashReset(User $user, string $plainPassword): string;

    public function isValid(User $user, string $plainPassword): bool;
}