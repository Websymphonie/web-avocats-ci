<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\Activation;

use Websymphonie\IdentityContext\Application\Service\Activation\ClockInterface;
use Websymphonie\IdentityContext\Application\Usecase\Command\Activation\ActivateAccountCommand;
use Websymphonie\IdentityContext\Domain\Exception\Activation\AccountActivationUnavailableException;
use Websymphonie\IdentityContext\Domain\Repository\Activation\AccountActivationRepositoryInterface;
use Websymphonie\IdentityContext\Domain\Service\Password\PasswordHashInterface;
use Websymphonie\IdentityContext\Infrastructure\Validator\Activation\ActivateAccountValidator;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ActivateAccountHandler implements CommandHandler
{
    public function __construct(private AccountActivationRepositoryInterface $repository, private PasswordHashInterface $hash, private ActivateAccountValidator $validator, private ClockInterface $clock) {}
    public function __invoke(ActivateAccountCommand $command): void
    {
        $this->validator->validate($command);
        $activation = $this->repository->findBySelector($command->selector);
        $now = $this->clock->now();
        if ($activation === null || !$activation->matchesToken($command->token) || !$activation->isUsable($now) || $activation->getUser()->getEnabled() || $activation->getUser()->getAccountMustBeVerifedBefore() === null) {
            throw new AccountActivationUnavailableException();
        }
        try {
            $this->repository->activate($activation, $command->token, $this->hash->hash($activation->getUser(), $command->password ?? ''), $now);
        } catch (\RuntimeException $exception) {
            throw new AccountActivationUnavailableException();
        }
    }
}
