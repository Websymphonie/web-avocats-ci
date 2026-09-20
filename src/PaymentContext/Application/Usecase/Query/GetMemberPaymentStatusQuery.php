<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Query;

final readonly class GetMemberPaymentStatusQuery
{
    public function __construct(public string $uuid)
    {
    }
}
