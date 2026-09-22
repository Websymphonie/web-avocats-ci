<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContactContext\Application\Service;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContactContext\Application\Service\ContactMessageDelivery;
use Websymphonie\ContactContext\Application\Usecase\CommandHandler\ContactMessageEmail;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;

final class ContactMessageDeliveryTest extends TestCase
{
    public function testBuildsTheSameControlledContactEmailForEveryDelivery(): void
    {
        $message = new ContactMessage(1, 'contact-uuid', 'Awa Koné', 'awa@example.test', null, 'Question', 'Bonjour', new DateTimeImmutable(), new DateTimeImmutable());
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::once())->method('send')->with(self::callback(static function (ContactMessageEmail $email): bool {
            return (string) $email->recipient() === 'contact@example.test'
                && (string) $email->replyTo() === 'awa@example.test'
                && $email->template() === 'contact/message';
        }));

        (new ContactMessageDelivery($mailer, 'contact@example.test'))->send($message);
    }
}
