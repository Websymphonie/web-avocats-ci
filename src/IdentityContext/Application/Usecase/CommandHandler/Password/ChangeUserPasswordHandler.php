<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Password;

use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\IdentityContext\Application\Usecase\Command\Password\ChangeUserPasswordCommand;
use Websymphonie\IdentityContext\Domain\Event\PasswordChangedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\ChangeUserPasswordValidator;
use Websymphonie\IdentityContext\Presenter\Service\Password\PasswordChange;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;


final readonly class ChangeUserPasswordHandler implements CommandHandler
{
    public function __construct(
        private CurrentUserProvider          $currentUserProvider,
        private UserModelRepositoryInterface $repository,
        private ChangeUserPasswordValidator  $validator,
        private EventDispatcher              $eventDispatcher,
        private PasswordChange               $passwordChanger
    )
    {
    }

    public function __invoke(ChangeUserPasswordCommand $command): User
    {
        $author = $this->currentUserProvider->getUser();
        $this->validator->validate($command);

        $user = $this->repository->getById($command->id);

        $resultUser = $this->passwordChanger->changePassword($user, $command->password);

        $resultUser->emitEvent(new PasswordChangedEvent((int) $resultUser->getId(), (int) $author->getId()));
        $this->eventDispatcher->dispatch($resultUser->releaseEvents());
        return $resultUser;
    }
}
