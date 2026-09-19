<?php

declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUsersCommand;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteUsersHandler implements CommandHandler
{
    public function __construct(private UserModelRepositoryInterface $repository)
    {
    }

    public function __invoke(DeleteUsersCommand $command): int
    {
        $users = $this->repository->findByIds($command->ids);

        foreach ($users as $user) {
            $this->repository->remove($user);
        }

        return count($users);
    }
}
