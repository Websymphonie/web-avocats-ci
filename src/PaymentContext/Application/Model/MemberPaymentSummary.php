<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

use DateTimeImmutable;

final readonly class MemberPaymentSummary
{
    public function __construct(
        public string $trainingTitle,
        public int $amount,
        public string $currency,
        public ?DateTimeImmutable $createdAt,
        public string $paymentStatusLabel,
        public string $paymentStatusVariant,
        public string $accessStatusLabel,
        public string $accessStatusVariant,
    ) {
    }
}
