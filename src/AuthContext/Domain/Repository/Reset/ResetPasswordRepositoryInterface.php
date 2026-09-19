<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Repository\Reset;

use DateTimeImmutable;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;

interface ResetPasswordRepositoryInterface
{
    public function getBySelector(string $selector): ?ResetPassword;

    public function saveIssued(ResetPassword $resetPassword): ResetPassword;

    public function consumeAndUpdate(ResetPassword $resetPassword, string $secret, string $hashedPassword, DateTimeImmutable $at): User;

    public function getById(int $id): ResetPassword;

    public function create(ResetPassword $entity): ResetPassword;

    public function update(ResetPassword $entity): ResetPassword;

    public function remove(ResetPassword $entity): void;

}
