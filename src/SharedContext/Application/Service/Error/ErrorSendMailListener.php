<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Error;

use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;
use Websymphonie\SharedContext\Domain\Service\Event\ErrorSendMailEvent;
use Websymphonie\SharedContext\Domain\Service\EventListener\EventListener;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class ErrorSendMailListener implements EventListener
{
    public function __construct(private Mailer $mailer)
    {
    }

    public function __invoke(ErrorSendMailEvent $event): void
    {
        $email = new ErrorSendEmail(
            recipient: new Email($event->getEmail()),
            title: $event->getErrorTitle(),
            errerMessage: $event->getErrorMessage(),
        );
        $this->mailer->send($email);
    }
}