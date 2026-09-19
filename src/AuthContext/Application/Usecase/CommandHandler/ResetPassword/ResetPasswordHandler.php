<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Usecase\CommandHandler\ResetPassword;

use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\ResetPasswordCommand;
use Websymphonie\AuthContext\Domain\Exception\ResetPasswordUnavailableException;
use Websymphonie\AuthContext\Domain\Repository\Reset\ResetPasswordRepositoryInterface;
use Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword\ResetPasswordValidator;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Service\Password\PasswordHasher;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

readonly class ResetPasswordHandler implements CommandHandler
{
    public function __construct(
        private ResetPasswordValidator           $validator,
        private PasswordHasher                   $hasher,
        private ResetPasswordRepositoryInterface $resetPasswordRepository,
        private ClockInterface $clock,
    )
    {
    }

    public function __invoke(ResetPasswordCommand $command): User
    {
        $this->validator->validate($command);

        $selector = trim((string) $command->selector);
        $secret = (string) $command->secret;
        $resetPassword = $this->resetPasswordRepository->getBySelector($selector);
        $now = $this->clock->now();
        $user = $resetPassword?->getUser();
        if ($resetPassword === null || !$resetPassword->matchesSecret($secret) || !$resetPassword->isUsable($now) || !$user instanceof User || !$user->getEnabled()) {
            throw new ResetPasswordUnavailableException('Reset token unavailable.');
        }
        $passwordHash = $this->hasher->hashReset($user, $command->password);
        try {
            return $this->resetPasswordRepository->consumeAndUpdate($resetPassword, $secret, $passwordHash, $now);
        } catch (\Throwable $exception) {
            throw new ResetPasswordUnavailableException('Reset token unavailable.', previous: $exception);
        }
    }
}
