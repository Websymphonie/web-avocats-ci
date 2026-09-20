<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\InvalidPaymentTransitionException;
use Websymphonie\PaymentContext\Domain\Model\Payment;

final class PaymentTest extends TestCase
{
    public function testPendingPaymentCanBeConfirmedAndConfirmationIsIdempotent(): void
    {
        $payment = new Payment(1, 'payment-uuid', 10, 20, 30, 15000, 'XOF', provider: PaymentProvider::FAKE, idempotencyKey: 'checkout-1');
        $now = new DateTimeImmutable('2026-09-20 10:00:00');

        $payment->confirm($now);
        $payment->confirm(new DateTimeImmutable('2026-09-20 11:00:00'));

        self::assertSame(PaymentStatus::CONFIRMED, $payment->status);
        self::assertSame($now, $payment->confirmedAt);
        self::assertNull($payment->failedAt);
    }

    public function testFailedPaymentCannotBeConfirmed(): void
    {
        $payment = new Payment(1, 'payment-uuid', 10, 20, 30, 15000, 'XOF');

        $payment->fail();

        $this->expectException(InvalidPaymentTransitionException::class);
        $payment->confirm();
    }

    public function testConfirmedPaymentCannotBeFailed(): void
    {
        $payment = new Payment(1, 'payment-uuid', 10, 20, 30, 15000, 'XOF');
        $payment->confirm();

        $this->expectException(InvalidPaymentTransitionException::class);
        $payment->fail();
    }
}
