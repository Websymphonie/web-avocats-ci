<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Exception;
use Websymphonie\IdentityContext\Application\Service\User\AdministrativeAccountProtection;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateUserCommand;
use Websymphonie\IdentityContext\Domain\Event\UserRoleAssignedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleRemovedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\UpdateUserValidator;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class UpdateUserHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private UpdateUserValidator          $validator,
        private UserServiceInterface         $userService,
        private AccountActivationRepositoryInterface $activationRepository,
        private ClockInterface $clock,
        private RememberMeTokenRevoker $rememberMeTokenRevoker,
        private AdministrativeAccountProtection $accountProtection,
        private EventDispatcher $eventDispatcher,
        private CurrentActorProvider $actorProvider,
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
        $this->accountProtection->assertCanUpdate($user, $command->roles, (bool) $command->enabled);
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
        $result = $this->repository->update($updated);
        $previousRoleSet = array_values(array_unique($previousRoles));
        $currentRoleSet = array_values(array_unique($currentRoles));

        foreach (array_diff($currentRoleSet, $previousRoleSet) as $role) {
            $result->emitEvent(new UserRoleAssignedEvent(
                userId: (int) $result->getId(),
                role: $role,
                actorUserId: $this->actorProvider->currentUserId(),
            ));
        }
        foreach (array_diff($previousRoleSet, $currentRoleSet) as $role) {
            $result->emitEvent(new UserRoleRemovedEvent(
                userId: (int) $result->getId(),
                role: $role,
                actorUserId: $this->actorProvider->currentUserId(),
            ));
        }

        $this->eventDispatcher->dispatch($result->releaseEvents());

        return $result;
    }
}
