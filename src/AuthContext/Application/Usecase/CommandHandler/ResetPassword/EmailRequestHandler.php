<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Usecase\CommandHandler\ResetPassword;


use DateTimeImmutable;
use Websymphonie\AuthContext\Application\Usecase\Command\ResetPassword\EmailRequestCommand;
use Websymphonie\AuthContext\Domain\Event\ResetCreatedEvent;
use Websymphonie\AuthContext\Domain\Repository\Reset\ResetPasswordRepositoryInterface;
use Websymphonie\AuthContext\Infrastructure\Persistence\Doctrine\Entity\ResetPassword;
use Websymphonie\AuthContext\Infrastructure\Validator\ResetPassword\EmailValidator;
use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\SecretGeneratorInterface;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

readonly class EmailRequestHandler implements CommandHandler
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private ResetPasswordRepositoryInterface $resetPasswordRepository,
        private EmailValidator               $validator,
        private EventDispatcher              $dispatcher,
        private SecretGeneratorInterface     $generator,
        private ?ClockInterface $clock = null,
        private string $resetTtl = '30 minutes',
    )
    {
    }

    public function __invoke(EmailRequestCommand $command): ?User
    {
        $command->email = strtolower(trim((string) $command->email));
        $this->validator->validate($command);
        // On vérifie si l'email existe déja
        $user = $this->repository->getByEmail((string) $command->email);
        if ($user === null || ($this->clock !== null && !$user->getEnabled())) {
            return null;
        }

        $resetPassword = $user->getResetPassword() ?? new ResetPassword();
        $now = $this->clock?->now() ?? new DateTimeImmutable();
        $secret = $this->generator->generateToken(64)->token;
        $selector = bin2hex(random_bytes(16));
        $resetPassword->setSelector($selector);
        $resetPassword->setTokenHash(hash('sha256', $secret));
        $resetPassword->setConsumedAt(null);
        $resetPassword->setPasswordResetRequestedAt($now);
        $expiresAt = $now->modify($this->resetTtl);
        $resetPassword->setPasswordResetExpiresAt($expiresAt);
        $user->setResetPassword($resetPassword);
        if ($this->clock === null) {
            $this->resetPasswordRepository->create($resetPassword);
        } else {
            $this->resetPasswordRepository->saveIssued($resetPassword);
        }
        $user->emitEvent(new ResetCreatedEvent(userId: $user->getId(), selector: $selector, secret: $secret, expiresAt: $expiresAt));
        $this->dispatcher->dispatch($user->releaseEvents());
        return $user;
    }
}
