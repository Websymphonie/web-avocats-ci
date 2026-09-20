<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Query;

final readonly class GetPaymentDetailsQuery
{
    public function __construct(public string $uuid) {}
}
