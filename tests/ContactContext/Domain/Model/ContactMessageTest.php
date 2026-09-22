<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContactContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;

final class ContactMessageTest extends TestCase
{
    public function testNewMessageIsPendingAndKeepsConsent(): void
    {
        $consentAt = new DateTimeImmutable('-1 minute');
        $message = new ContactMessage(0, '', 'Awa Koné', 'awa@example.test', null, 'Question', 'Bonjour', $consentAt, new DateTimeImmutable());

        self::assertSame(ContactMessageDeliveryStatus::PENDING, $message->deliveryStatus);
        self::assertSame($consentAt, $message->consentAt);
        self::assertNull($message->sentAt);
    }

    public function testMarkSentSetsStatusAndSentAt(): void
    {
        $message = $this->message();
        $sentAt = new DateTimeImmutable();

        $message->markSent($sentAt);

        self::assertSame(ContactMessageDeliveryStatus::SENT, $message->deliveryStatus);
        self::assertSame($sentAt, $message->sentAt);
    }

    public function testMarkFailedKeepsMessageAndClearsSentAt(): void
    {
        $message = $this->message();
        $message->markSent(new DateTimeImmutable());

        $message->markFailed();

        self::assertSame(ContactMessageDeliveryStatus::FAILED, $message->deliveryStatus);
        self::assertNull($message->sentAt);
        self::assertSame('Bonjour', $message->message);
    }

    private function message(): ContactMessage
    {
        return new ContactMessage(1, 'uuid', 'Awa Koné', 'awa@example.test', '+225 00 00 00 00', 'Question', 'Bonjour', new DateTimeImmutable(), new DateTimeImmutable());
    }
}
