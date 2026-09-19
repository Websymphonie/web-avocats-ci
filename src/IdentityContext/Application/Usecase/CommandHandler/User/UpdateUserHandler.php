<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Exception;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\UpdateUserValidator;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateUserHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private UpdateUserValidator          $validator,
        private UserServiceInterface         $userService,
        private AccountActivationRepositoryInterface $activationRepository,
        private ClockInterface $clock,
        private RememberMeTokenRevoker $rememberMeTokenRevoker,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(UpdateUserCommand $command): User
    {
        $this->validator->validate($command);
        $user = $this->repository->getById($command->id);
        $previousEmail = $user->getEmail();
        $previousEnabled = $user->getEnabled();
        $previousRoles = $user->getRoles();
        $updated = $this->userService->updateUser($user, $command);
        $currentRoles = $updated->getRoles();
        sort($previousRoles);
        sort($currentRoles);
        $rolesChanged = $previousRoles !== $currentRoles;
        $emailChanged = $previousEmail !== $updated->getEmail();
        $disabled = $previousEnabled === true && $updated->getEnabled() === false;
        if ($emailChanged || $rolesChanged || $disabled) {
            $updated->incrementSecurityVersion();
            if ($previousEmail !== null) {
                $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($previousEmail);
            }
            if ($updated->getEmail() !== null && $updated->getEmail() !== $previousEmail) {
                $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($updated->getEmail());
            }
        }
        if (!$updated->getEnabled() && $previousEmail !== $updated->getEmail()) {
            $this->activationRepository->invalidateForUser($updated, $this->clock->now());
        }
        return $this->repository->update($updated);
    }
}
