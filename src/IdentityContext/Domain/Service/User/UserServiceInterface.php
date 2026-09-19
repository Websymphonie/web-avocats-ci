<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Service\User;

use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;

interface UserServiceInterface
{
    public function addUser(User $user, AddUserCommand $command): User;

    public function updateUser(User $user, UpdateUserCommand $command): User;

    public function updateProfile(User $user, UpdateProfileCommand $command): User;
}