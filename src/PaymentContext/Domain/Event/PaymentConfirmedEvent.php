<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Event;

use DateTimeImmutable;

final readonly class PaymentConfirmedEvent
{
    public function __construct(
        public string $paymentUuid,
        public int $userId,
        public int $trainingId,
        public int $amount,
        public string $currency,
        public string $provider,
        public ?string $providerReference,
        public DateTimeImmutable $confirmedAt,
    ) {
    }
}
