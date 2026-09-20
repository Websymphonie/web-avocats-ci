<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;


use Websymphonie\IdentityContext\Application\Usecase\Command\User\DeleteUserCommand;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class DeleteUserHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private AdministrativeAccountProtection $accountProtection,
    )
    {
    }

    public function __invoke(DeleteUserCommand $command): void
    {
        $user = $this->repository->getById($command->id);
        $this->accountProtection->assertCanDelete($user);
        $this->repository->remove($user);
    }
}
