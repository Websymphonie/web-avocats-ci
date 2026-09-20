<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Service;

use Websymphonie\PaymentContext\Application\Model\CurrencyReference;

interface CurrencyCatalogInterface
{
    /** @return list<CurrencyReference> */
    public function listActive(): array;

    public function findActiveByCode(string $code): ?CurrencyReference;
}
