<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\CommandHandler;

use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\SharedContext\Application\Service\Mailing\EmailDefinition;
use Websymphonie\SharedContext\Application\Service\Mailing\ReplyToEmailDefinition;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\ValueObject\Email;

final readonly class ContactMessageEmail implements EmailDefinition, ReplyToEmailDefinition
{
    public function __construct(
        private Email $recipient,
        private Email $replyTo,
        private ContactMessage $message,
    ) {
    }

    public function recipient(): Email { return $this->recipient; }
    public function replyTo(): Email { return $this->replyTo; }
    public function subject(): string { return 'Nouveau message depuis le formulaire de contact'; }
    public function subjectVariables(): array { return []; }
    public function template(): string { return 'contact/message'; }
    public function templateVariables(): array { return ['contactMessage' => $this->message]; }
    public function locale(): string { return 'fr'; }
    public function getDomain(): string { return 'contact_context'; }
}
