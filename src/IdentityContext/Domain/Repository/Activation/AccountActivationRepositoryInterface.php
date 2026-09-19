<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Repository\Activation;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Activation\AccountActivation;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface AccountActivationRepositoryInterface
{
    public function create(AccountActivation $activation): AccountActivation;
    public function findBySelector(string $selector): ?AccountActivation;
    public function invalidateForUser(User $user, DateTimeImmutable $at): void;
    public function activate(AccountActivation $activation, string $rawToken, string $hashedPassword, DateTimeImmutable $at): void;
}
