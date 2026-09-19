<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\User;

use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

readonly class UserService implements UserServiceInterface
{
    public function addUser(User $user, AddUserCommand $command): User
    {
        $user->setName($command->name ?? null);
        $user->setEmail($command->email ?? null);
        $user->setEnabled($command->enabled ?? null);
        $user->setRoles($command->roles ?? []);
        return $user;
    }

    public function updateUser(User $user, UpdateUserCommand $command): User
    {
        $user->setName($command->name ?? null);
        $user->setEmail($command->email ?? null);
        $user->setEnabled($command->enabled ?? null);
        $user->setRoles($command->roles ?? []);

        return $user;
    }

    public function updateProfile(User $user, UpdateProfileCommand $command): User
    {
        $user->setName($command->name ?? null);
        $user->setEmail($command->email ?? null);

        return $user;
    }
}