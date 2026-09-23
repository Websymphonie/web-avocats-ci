<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Exception;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
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
use Websymphonie\LawyerContext\Application\Service\LawyerProfileProvisioner;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
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
        private AdministrativeAccountProtection $accountProtection,
        private EventDispatcher              $dispatcher,
        private AccountActivationIssuer      $activationIssuer,
        private ?CurrentActorProvider        $actorProvider = null,
        private ?LawyerProfileProvisioner     $lawyerProfileProvisioner = null,
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
        $this->accountProtection->assertCanCreateWithRoles($command->roles);
        $this->emailVerified->assertNotUsed($command->email);
        $user = new User();
        $user = $this->userService->addUser($user, $command);
        $user->setEnabled(false);
        $user->setPassword($this->hash->hash($user, bin2hex(random_bytes(32))));
        $resultUser = $this->repository->create($user);
        if (in_array('ROLE_AVOCAT', $resultUser->getRoles(), true)) {
            $this->lawyerProfileProvisioner?->ensureFor($resultUser);
        }
        $issued = $this->activationIssuer->issue($resultUser);
        $this->repository->update($resultUser);

        foreach ($resultUser->getRoles() as $role) {
            $resultUser->emitEvent(new UserRoleAssignedEvent(
                userId: (int) $resultUser->getId(),
                role: $role,
                actorUserId: $this->actorProvider?->currentUserId(),
            ));
        }

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
