<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContactContext\Application\Usecase\CommandHandler;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Websymphonie\ContactContext\Application\Service\ContactThrottleInterface;
use Websymphonie\ContactContext\Application\Usecase\Command\SubmitContactMessageCommand;
use Websymphonie\ContactContext\Application\Usecase\CommandHandler\ContactMessageEmail;
use Websymphonie\ContactContext\Application\Usecase\CommandHandler\SubmitContactMessageHandler;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Mailing\Mailer;

final class SubmitContactMessageHandlerTest extends TestCase
{
    public function testMessageIsPersistedBeforeSuccessfulDelivery(): void
    {
        $message = $this->message();
        $repository = $this->repository($message);
        $throttle = $this->createMock(ContactThrottleInterface::class);
        $throttle->method('consume')->willReturn(true);
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects(self::once())->method('send')->with(self::callback(static function (ContactMessageEmail $email): bool {
            return (string) $email->replyTo() === 'awa@example.test';
        }));

        $result = (new SubmitContactMessageHandler($repository, $throttle, $mailer, 'contact@example.test', new NullLogger()))(new SubmitContactMessageCommand(email: 'awa@example.test', fullName: 'Awa Koné', subject: 'Question', message: 'Bonjour', consent: true));

        self::assertSame(ContactMessageDeliveryStatus::SENT, $result->deliveryStatus);
        self::assertNotNull($result->sentAt);
    }

    public function testMailerFailureKeepsTheMessageAndMarksItFailed(): void
    {
        $message = $this->message();
        $repository = $this->repository($message);
        $throttle = $this->createMock(ContactThrottleInterface::class);
        $throttle->method('consume')->willReturn(true);
        $mailer = $this->createMock(Mailer::class);
        $mailer->method('send')->willThrowException(new \RuntimeException('SMTP indisponible'));

        $result = (new SubmitContactMessageHandler($repository, $throttle, $mailer, 'contact@example.test', new NullLogger()))(new SubmitContactMessageCommand(email: 'awa@example.test', fullName: 'Awa Koné', subject: 'Question', message: 'Bonjour', consent: true));

        self::assertSame(ContactMessageDeliveryStatus::FAILED, $result->deliveryStatus);
        self::assertSame('Bonjour', $result->message);
        self::assertNull($result->sentAt);
    }

    private function repository(ContactMessage $persisted): ContactMessageRepositoryInterface
    {
        $repository = $this->createMock(ContactMessageRepositoryInterface::class);
        $repository->expects(self::exactly(2))->method('save')->willReturn($persisted);
        return $repository;
    }

    private function message(): ContactMessage
    {
        return new ContactMessage(1, 'uuid', 'Awa Koné', 'awa@example.test', null, 'Question', 'Bonjour', new DateTimeImmutable(), new DateTimeImmutable());
    }
}
