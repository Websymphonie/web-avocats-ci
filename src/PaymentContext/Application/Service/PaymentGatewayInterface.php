<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

use Websymphonie\PaymentContext\Application\Model\PaymentInitialization;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Model\Payment;

interface PaymentGatewayInterface
{
    public function provider(): PaymentProvider;

    public function initializePayment(Payment $payment): PaymentInitialization;
}
