<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Twig\Currency;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Websymphonie\SharedContext\Domain\Enum\CurrencyEnum;
use Websymphonie\SharedContext\Domain\Service\Currency\CurrencyServiceInterface;
use Websymphonie\SharedContext\Presenter\Service\Helper\HelpersServices;

class CurrencyExtension extends AbstractExtension
{
    public function __construct(
        private readonly CurrencyServiceInterface $currencyService,
    )
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('price', $this->getPrice(...)),
            new TwigFilter('amountToLetter', $this->getAmountToLetter(...)),
            new TwigFilter('currency_label', $this->formatCurrencyLabel(...)),
            new TwigFilter('ucTowords', fn(string $text) => HelpersServices::toUcWords($text)),
        ];
    }

    public function getPrice(int $amount, ?string $currencyCode = null): string
    {
        return $this->currencyService->formatCurrency($amount, $currencyCode);
    }

    public function formatCurrencyLabel(?string $currency): string
    {
        $currency = strtoupper(trim((string)$currency));

        return $currency === CurrencyEnum::DEVISE->value ? CurrencyEnum::DEVISE_SYMBOL->value : $currency;
    }

    public function getAmountToLetter(float|int $amount): string
    {
        return $this->currencyService->toLetter($amount);
    }
}
