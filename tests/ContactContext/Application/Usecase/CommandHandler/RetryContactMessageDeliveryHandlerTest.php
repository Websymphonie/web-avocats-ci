<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContactContext\Application\Usecase\CommandHandler;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Websymphonie\ContactContext\Application\Service\ContactMessageDeliveryInterface;
use Websymphonie\ContactContext\Application\Usecase\Command\RetryContactMessageDeliveryCommand;
use Websymphonie\ContactContext\Application\Usecase\CommandHandler\RetryContactMessageDeliveryHandler;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;

final class RetryContactMessageDeliveryHandlerTest extends TestCase
{
    public function testSuccessfulRetryMarksMessageAsSentAndAuditsIt(): void
    {
        $message = $this->failedMessage();
        $repository = $this->createMock(ContactMessageRepositoryInterface::class);
        $repository->expects(self::once())->method('claimForRetry')->with('contact-uuid')->willReturnCallback(function () use ($message): ContactMessage {
            $message->prepareForRetry();
            return $message;
        });
        $repository->expects(self::once())->method('save')->willReturnArgument(0);
        $delivery = $this->createMock(ContactMessageDeliveryInterface::class);
        $delivery->expects(self::once())->method('send')->with($message);
        $events = $this->createMock(EventDispatcherInterface::class);
        $events->expects(self::once())->method('dispatch');

        $result = (new RetryContactMessageDeliveryHandler($repository, $delivery, new NullLogger(), $events))(new RetryContactMessageDeliveryCommand('contact-uuid'));

        self::assertSame(ContactMessageDeliveryStatus::SENT, $result->deliveryStatus);
        self::assertNotNull($result->sentAt);
    }

    public function testFailedRetryKeepsMessageFailedAndAuditsIt(): void
    {
        $message = $this->failedMessage();
        $repository = $this->createMock(ContactMessageRepositoryInterface::class);
        $repository->expects(self::once())->method('claimForRetry')->willReturnCallback(function () use ($message): ContactMessage {
            $message->prepareForRetry();
            return $message;
        });
        $repository->expects(self::once())->method('save')->willReturnArgument(0);
        $delivery = $this->createMock(ContactMessageDeliveryInterface::class);
        $delivery->method('send')->willThrowException(new \RuntimeException('SMTP unavailable'));
        $events = $this->createMock(EventDispatcherInterface::class);
        $events->expects(self::once())->method('dispatch');

        $result = (new RetryContactMessageDeliveryHandler($repository, $delivery, new NullLogger(), $events))(new RetryContactMessageDeliveryCommand('contact-uuid'));

        self::assertSame(ContactMessageDeliveryStatus::FAILED, $result->deliveryStatus);
        self::assertNull($result->sentAt);
        self::assertSame('Message de test', $result->message);
    }

    private function failedMessage(): ContactMessage
    {
        $message = new ContactMessage(1, 'contact-uuid', 'Awa Koné', 'awa@example.test', null, 'Question', 'Message de test', new DateTimeImmutable(), new DateTimeImmutable());
        $message->markFailed();
        return $message;
    }
}
