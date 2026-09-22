<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Service;

use Websymphonie\ContactContext\Application\Usecase\CommandHandler\ContactMessageEmail;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class ContactMessageDelivery implements ContactMessageDeliveryInterface
{
    public function __construct(
        private Mailer $mailer,
        private string $contactRecipientEmail,
    ) {
    }

    public function send(ContactMessage $message): void
    {
        $this->mailer->send(new ContactMessageEmail(
            recipient: new Email($this->contactRecipientEmail),
            replyTo: new Email($message->email),
            message: $message,
        ));
    }
}
