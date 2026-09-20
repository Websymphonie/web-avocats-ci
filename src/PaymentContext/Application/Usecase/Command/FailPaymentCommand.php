<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Command;

final readonly class FailPaymentCommand
{
    public function __construct(public string $paymentUuid) {}
}
