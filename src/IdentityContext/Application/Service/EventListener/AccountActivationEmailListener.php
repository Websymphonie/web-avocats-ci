<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Application\Service\EventListener;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Websymphonie\IdentityContext\Application\Service\Email\AccountActivationEmail;
use Websymphonie\IdentityContext\Domain\Event\AccountActivationRequestedEvent;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;
use Websymphonie\SharedContext\Domain\Service\EventListener\EventListener;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class AccountActivationEmailListener implements EventListener
{
    public function __construct(private UserModelRepositoryInterface $repository, private Mailer $mailer, private UrlGeneratorInterface $router) {}
    public function __invoke(AccountActivationRequestedEvent $event): void
    {
        $user = $this->repository->getById($event->userId);
        $url = $this->router->generate('app_account_activate', ['selector' => $event->selector, 'token' => $event->token], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->mailer->send(new AccountActivationEmail(new Email((string) $user->getEmail()), $user, $url, $event->expiresAt));
    }
}
