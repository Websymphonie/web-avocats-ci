<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Event;

final readonly class PaymentFailedEvent
{
    public function __construct(
        public string $paymentUuid,
        public int $userId,
        public int $trainingId,
        public int $amount,
        public string $currency,
        public string $trainingTitle,
    ) {
    }
}
