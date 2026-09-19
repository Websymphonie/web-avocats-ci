<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Exception;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\IdentityContext\Application\Service\Activation\AccountActivationIssuer;
use Websymphonie\IdentityContext\Domain\Event\AccountActivationRequestedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleAssignedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\PasswordHashInterface;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\AddUserValidator;
use Websymphonie\IdentityContext\Presenter\Service\EmailVerified;
use Websymphonie\IdentityContext\Presenter\Tiwg\Extension\RolesExtension;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class AddUserHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private AddUserValidator             $validator,
        private PasswordHashInterface        $hash,
        private EmailVerified                $emailVerified,
        private UserServiceInterface         $userService,
        private RolesExtension               $rolesExtension,
        private EventDispatcher              $dispatcher,
        private AccountActivationIssuer      $activationIssuer,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(AddUserCommand $command): void
    {
        // On valide les données ici
        $this->validator->validate($command);
        $this->emailVerified->assertNotUsed($command->email);
        $user = new User();
        $user = $this->userService->addUser($user, $command);
        $user->setEnabled(false);
        $user->setPassword($this->hash->hash($user, bin2hex(random_bytes(32))));
        $resultUser = $this->repository->create($user);
        $issued = $this->activationIssuer->issue($resultUser);
        $this->repository->update($resultUser);

        $resultUser->emitEvent(new UserRoleAssignedEvent(
            userId: $resultUser->getId(),
            role: $this->rolesExtension->mainRole($user)->value,
        ));

        if ($command->sendMail) {
            $resultUser->emitEvent(new AccountActivationRequestedEvent(
                userId: $resultUser->getId(),
                selector: $issued['activation']->getSelector(),
                token: $issued['token'],
                expiresAt: $issued['activation']->getExpiresAt(),
            ));
        }

        $this->dispatcher->dispatch($resultUser->releaseEvents());
    }
}
