<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

use Websymphonie\PaymentContext\Application\Model\VerifiedPaymentTransaction;

interface PaymentTransactionVerifierInterface
{
    public function verify(string $transactionId): VerifiedPaymentTransaction;
}
