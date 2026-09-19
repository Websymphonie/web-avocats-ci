<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Exception;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\UpdateProfileCommand;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\User\UserServiceInterface;
use Websymphonie\IdentityContext\Infrastructure\Validator\User\UserProfileValidator;
use Websymphonie\IdentityContext\Infrastructure\Security\RememberMeTokenRevoker;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateProfileHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private UserProfileValidator         $validator,
        private UserServiceInterface         $userService,
        private RememberMeTokenRevoker        $rememberMeTokenRevoker,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(UpdateProfileCommand $command): void
    {
        $this->validator->validate($command);
        $user = $this->repository->getById($command->id);
        $previousEmail = $user->getEmail();
        $updated = $this->userService->updateProfile($user, $command);
        if ($previousEmail !== $updated->getEmail()) {
            $updated->incrementSecurityVersion();
            if ($previousEmail !== null) {
                $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($previousEmail);
            }
            if ($updated->getEmail() !== null) {
                $this->rememberMeTokenRevoker->revokeAllForUserIdentifier($updated->getEmail());
            }
        }
        $this->repository->update($updated);
    }
}
