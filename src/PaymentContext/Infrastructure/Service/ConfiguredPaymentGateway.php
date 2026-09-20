<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use LogicException;
use Websymphonie\PaymentContext\Application\Model\PaymentInitialization;
use Websymphonie\PaymentContext\Application\Service\PaymentGatewayInterface;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Model\Payment;

final readonly class ConfiguredPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private FakePaymentGateway $fake,
        private KkiaPayPaymentGateway $kkiapay,
        private string $paymentProvider,
    ) {
    }

    public function provider(): PaymentProvider
    {
        return $this->gateway()->provider();
    }

    public function initializePayment(Payment $payment): PaymentInitialization
    {
        return $this->gateway()->initializePayment($payment);
    }

    private function gateway(): PaymentGatewayInterface
    {
        return match (strtoupper(trim($this->paymentProvider))) {
            PaymentProvider::FAKE->value => $this->fake,
            PaymentProvider::KKIAPAY->value => $this->kkiapay,
            default => throw new LogicException(sprintf('Payment provider "%s" is not supported.', $this->paymentProvider)),
        };
    }
}
