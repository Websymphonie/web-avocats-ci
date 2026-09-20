<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Websymphonie\PaymentContext\Application\Model\PaymentFulfillmentResult;
use Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\FulfillConfirmedPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\CommandHandler\FulfillConfirmedPaymentHandler;
use Websymphonie\PaymentContext\Domain\Enum\PaymentFulfillmentStatus;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;

final class FulfillConfirmedPaymentHandlerTest extends TestCase
{
    public function testLearningFailureLeavesConfirmedPaymentPending(): void
    {
        $payment = $this->confirmedPayment();
        $repository = $this->repository($payment);
        $granter = $this->createMock(PaidTrainingAccessGranterInterface::class);
        $granter->expects(self::once())->method('grant')->willThrowException(new \RuntimeException('Learning indisponible'));

        $handler = new FulfillConfirmedPaymentHandler($repository, $granter, new NullLogger());

        try {
            $handler(new FulfillConfirmedPaymentCommand($payment->uuid));
            self::fail('Le fulfillment doit remonter l’échec technique au retryant.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Learning indisponible', $exception->getMessage());
        }

        self::assertSame(PaymentStatus::CONFIRMED, $payment->status);
        self::assertSame(PaymentFulfillmentStatus::PENDING, $payment->fulfillmentStatus);
        self::assertSame(1, $payment->fulfillmentAttempts);
    }

    public function testRetryCompletesFulfillmentAndCompletedPaymentIsNoOp(): void
    {
        $payment = $this->confirmedPayment();
        $repository = $this->repository($payment);
        $granter = $this->createMock(PaidTrainingAccessGranterInterface::class);
        $granter->expects(self::once())->method('grant');

        $handler = new FulfillConfirmedPaymentHandler($repository, $granter, new NullLogger());

        self::assertSame(PaymentFulfillmentResult::COMPLETED, $handler(new FulfillConfirmedPaymentCommand($payment->uuid)));
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, $payment->fulfillmentStatus);
        self::assertSame(PaymentFulfillmentResult::ALREADY_COMPLETED, $handler(new FulfillConfirmedPaymentCommand($payment->uuid)));
    }

    /** @return PaymentRepositoryInterface&MockObject */
    private function repository(Payment $payment): PaymentRepositoryInterface
    {
        $repository = $this->createMock(PaymentRepositoryInterface::class);
        $repository->method('getByUuid')->willReturn($payment);
        $repository->method('save')->willReturnCallback(static fn (Payment $value): Payment => $value);
        return $repository;
    }

    private function confirmedPayment(): Payment
    {
        $payment = new Payment(1, 'payment-uuid', 10, 20, 30, 15000, 'XOF');
        $payment->confirm();
        return $payment;
    }
}
