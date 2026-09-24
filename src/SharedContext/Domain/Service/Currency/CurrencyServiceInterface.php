<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\Currency;

use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;

interface CurrencyServiceInterface
{
    public function currency(): ?CurrencyModel;

    public function toLetter(int $amount): string;

    public function formatCurrency(int $amount, ?string $currencyCode = null): string;
}
