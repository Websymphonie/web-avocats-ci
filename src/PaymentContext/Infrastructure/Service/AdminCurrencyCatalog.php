<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;
use Websymphonie\AdminContext\Domain\Repository\Currencies\CurrencyModelRepositoryInterface;
use Websymphonie\PaymentContext\Application\Model\CurrencyReference;
use Websymphonie\PaymentContext\Application\Service\CurrencyCatalogInterface;

final readonly class AdminCurrencyCatalog implements CurrencyCatalogInterface
{
    public function __construct(private CurrencyModelRepositoryInterface $currencies)
    {
    }

    /** @return list<CurrencyReference> */
    public function listActive(): array
    {
        $references = [];
        foreach ($this->currencies->listActive() as $currency) {
            $reference = $this->toReference($currency);
            if ($reference !== null) {
                $references[] = $reference;
            }
        }

        return $references;
    }

    public function findActiveByCode(string $code): ?CurrencyReference
    {
        return $this->toReference($this->currencies->findActiveByCode($code));
    }

    private function toReference(?CurrencyModel $currency): ?CurrencyReference
    {
        if ($currency === null || $currency->currencyCode === null) {
            return null;
        }

        $code = strtoupper(trim($currency->currencyCode));
        if ($code === '') {
            return null;
        }

        return new CurrencyReference(
            code: $code,
            name: trim((string) $currency->currencyName),
            active: $currency->isActive === true,
        );
    }
}
