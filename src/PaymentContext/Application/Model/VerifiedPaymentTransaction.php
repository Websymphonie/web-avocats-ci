<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

final readonly class VerifiedPaymentTransaction
{
    public function __construct(
        public string $transactionId,
        public bool $successful,
        public int $amount,
        public string $partnerId,
        public ?string $currency = null,
    ) {
    }
}
