<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Command;

final readonly class FulfillConfirmedPaymentCommand
{
    public function __construct(public string $paymentUuid)
    {
    }
}
