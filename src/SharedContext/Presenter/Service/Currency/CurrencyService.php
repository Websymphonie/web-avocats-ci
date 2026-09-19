<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Service\Currency;

use NumberToWords\Exception\InvalidArgumentException;
use NumberToWords\Exception\NumberToWordsException;
use NumberToWords\NumberToWords;
use Websymphonie\AdminContext\Domain\Model\Currency\CurrencyModel;
use Websymphonie\AdminContext\Domain\Repository\Currencies\CurrencyModelRepositoryInterface;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Enum\CurrencyEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Domain\Service\Currency\CurrencyServiceInterface;

readonly class CurrencyService implements CurrencyServiceInterface
{
    public function __construct(
        private CurrencyModelRepositoryInterface $repository,
        private CacheServiceInterface            $cacheService,
    )
    {
    }

    public function formatCurrency(int $amount): string
    {
        $currency = $this->currency();
        $decimalPlaces = max(0, $currency->decimalPlace ?? 0);

        $formattedAmount = number_format(
            num: $amount,
            decimals: $decimalPlaces,
            decimal_separator: ',',
            thousands_separator: ' ',
        );

        $leftSymbol = trim($currency->leftSymbol ?? '');
        $rightSymbol = trim($currency->rightSymbol ?? '');
        $currencyCode = trim($currency->currencyCode ?? CurrencyEnum::DEVISE->value);

        if ($currencyCode === CurrencyEnum::DEVISE->value) {
            $leftSymbol = strtoupper($leftSymbol) === CurrencyEnum::DEVISE->value ? '' : $leftSymbol;
            $rightSymbol = $rightSymbol === '' || strtoupper($rightSymbol) === CurrencyEnum::DEVISE->value ? CurrencyEnum::DEVISE_SYMBOL->value : $rightSymbol;
        }

        return match (true) {
            $leftSymbol !== '' => sprintf('%s %s', $leftSymbol, $formattedAmount),
            $rightSymbol !== '' => sprintf('%s %s', $formattedAmount, $rightSymbol),
            default => sprintf('%s %s', $formattedAmount, $currencyCode),
        };
    }

    public function currency(): ?CurrencyModel
    {
        $cacheKey = CacheEnum::CACHE_CURRENCY->value;
        return $this->cacheService->getCache($cacheKey, function () {
            return $this->repository->isActive();
        });
    }

    /**
     * @throws NumberToWordsException
     * @throws InvalidArgumentException
     */
    public function toLetter(int $amount): string
    {
        $currency = $this->currency();
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer(CurrencyEnum::LOCAL->value);
        $words = $numberTransformer->toWords($amount);
        $currencyCode = strtoupper(
            trim($currency->currencyCode ?? CurrencyEnum::DEVISE->value)
        );
        $currencyLabel = match ($currencyCode) {
            CurrencyEnum::DEVISE->value => 'francs CFA',
            default => $currencyCode,
        };
        return ucfirst(sprintf(
            '%s %s',
            $words,
            $currencyLabel
        ));
    }
}
