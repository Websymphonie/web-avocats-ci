<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Usecase\CommandHandler\User;

use Websymphonie\IdentityContext\Application\Service\Activation\AccountActivationIssuer;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\ResendAccountActivationCommand;
use Websymphonie\IdentityContext\Domain\Event\AccountActivationRequestedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class ResendAccountActivationHandler implements CommandHandler
{
    public function __construct(private UserModelRepositoryInterface $users, private AccountActivationIssuer $issuer, private EventDispatcher $dispatcher) {}
    public function __invoke(ResendAccountActivationCommand $command): void
    {
        $user = $this->users->getById($command->userId);
        if ($user->getEnabled()) { return; }
        $issued = $this->issuer->issue($user);
        $this->users->update($user);
        $this->dispatcher->dispatch([new AccountActivationRequestedEvent($user->getId(), $issued['activation']->getSelector(), $issued['token'], $issued['activation']->getExpiresAt())]);
    }
}
