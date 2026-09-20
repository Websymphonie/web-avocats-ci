<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\PaymentContext\Application\Model\PaymentInitialization;
use Websymphonie\PaymentContext\Application\Service\PaymentGatewayInterface;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Model\Payment;

final class FakePaymentGateway implements PaymentGatewayInterface
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::FAKE;
    }

    public function initializePayment(Payment $payment): PaymentInitialization
    {
        return new PaymentInitialization($this->provider(), 'fake_tx_' . bin2hex(random_bytes(12)), ['mode' => 'test']);
    }
}
