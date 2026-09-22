<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Mailer;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Websymphonie\SharedContext\Application\Service\Mailing\EmailDefinition;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;
use Websymphonie\SharedContext\Application\Service\Mailing\RenderedEmailDefinition;
use Websymphonie\SharedContext\Application\Service\Mailing\AttachmentEmailDefinition;
use Websymphonie\SharedContext\Application\Service\Mailing\ReplyToEmailDefinition;

final readonly class SymfonyMailer implements Mailer
{
    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface     $mailer,
        private string              $senderEmail,
        private string              $senderName,
    )
    {
    }

    /**
     * @param EmailDefinition $email
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(EmailDefinition $email): void
    {
        $sender = new Address(address: $this->senderEmail, name: $this->senderName);
        $htmlTemplate = sprintf('emails/%s.html.twig', $email->template());

        $message = (new TemplatedEmail())
            ->from($sender)
            ->to((string) $email->recipient())
            ->subject($this->translator->trans(
                $email->subject(),
                $email->subjectVariables(),
                $email->getDomain(),
                $email->locale(),
            ))
            ->htmlTemplate($htmlTemplate)->context(array_merge($email->templateVariables(), [
                'local' => $email->locale(),
                'domain' => $email->getDomain(),
                'email_app_name' => $this->senderName,
                'email_sender_address' => $this->senderEmail,
            ]));

        if ($email instanceof RenderedEmailDefinition) {
            $message->html($email->htmlBody())->text($email->textBody());
        }
        if ($email instanceof AttachmentEmailDefinition) {
            foreach ($email->attachments() as $attachment) {
                $message->attach($attachment['content'], $attachment['filename'], $attachment['mediaType']);
            }
        }
        if ($email instanceof ReplyToEmailDefinition) {
            $message->replyTo((string) $email->replyTo());
        }

        $this->mailer->send($message);
    }
}
