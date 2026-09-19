<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Application\Service\EventListener;

use Websymphonie\AuthContext\Application\Service\Email\ResetSendEmail;
use Websymphonie\AuthContext\Domain\Event\ResetCreatedEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Websymphonie\IdentityContext\Domain\Repository\User\UserModelRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;
use Websymphonie\SharedContext\Domain\Service\EventListener\EventListener;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class ResetCreateListener implements EventListener
{
    public function __construct(
        private UserModelRepositoryInterface $repository,
        private Mailer $mailer,
        private UrlGeneratorInterface $router,
    )
    {
    }

    public function __invoke(ResetCreatedEvent $event): void
    {
        // On envois le mail ici
        $user = $this->repository->getById($event->getUserId());
        $title = 'Réinitialisation de mot de passe';
        $resetPassword = $user->getResetPassword();
        if ($resetPassword === null) { return; }
        $url = $this->router->generate('password.reset', ['selector' => $event->selector, 'token' => $event->secret], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = new ResetSendEmail(
            recipient: new Email($user->getEmail()),
            title: $title,
            user: $user,
            resetPassword: $resetPassword,
            resetUrl: $url,
            expiresAt: $event->expiresAt,
        );

        $this->mailer->send($email);
    }
}
