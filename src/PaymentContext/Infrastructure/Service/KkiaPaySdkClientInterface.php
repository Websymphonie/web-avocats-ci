<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

interface KkiaPaySdkClientInterface
{
    public function verifyTransaction(string $transactionId): mixed;
}
