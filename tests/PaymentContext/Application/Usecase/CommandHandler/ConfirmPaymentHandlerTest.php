<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\CommandHandler\ConfirmPaymentHandler;
use Websymphonie\PaymentContext\Application\Usecase\CommandHandler\FulfillConfirmedPaymentHandler;
use Websymphonie\PaymentContext\Domain\Enum\PaymentFulfillmentStatus;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;

final class ConfirmPaymentHandlerTest extends TestCase
{
    public function testLearningFailureDoesNotTurnProviderConfirmationIntoPaymentFailure(): void
    {
        $payment = new Payment(1, 'payment-uuid', 10, 20, 30, 15000, 'XOF', provider: PaymentProvider::FAKE);
        $repository = $this->createMock(PaymentRepositoryInterface::class);
        $repository->method('getByUuid')->willReturn($payment);
        $repository->method('save')->willReturnCallback(static fn (Payment $value): Payment => $value);

        $granter = $this->createMock(PaidTrainingAccessGranterInterface::class);
        $granter->expects(self::once())->method('grant')->willThrowException(new \RuntimeException('Learning indisponible'));
        $fulfillmentHandler = new FulfillConfirmedPaymentHandler($repository, $granter, new NullLogger());
        $handler = new ConfirmPaymentHandler($repository, $fulfillmentHandler);

        $handler(new ConfirmPaymentCommand($payment->uuid, 'provider-reference'));

        self::assertSame(PaymentStatus::CONFIRMED, $payment->status);
        self::assertSame(PaymentFulfillmentStatus::PENDING, $payment->fulfillmentStatus);
        self::assertSame(1, $payment->fulfillmentAttempts);
    }
}
