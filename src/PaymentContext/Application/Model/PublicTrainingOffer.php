<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

final readonly class PublicTrainingOffer
{
    public function __construct(
        public int $amount,
        public string $currency,
    ) {
    }
}
