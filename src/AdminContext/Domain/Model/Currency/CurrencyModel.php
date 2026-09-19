<?php
declare(strict_types=1);

namespace Websymphonie\AdminContext\Domain\Model\Currency;

final class CurrencyModel
{
    public function __construct(
        public ?int    $id = null,
        public ?string $currencyCode = null,
        public ?string $currencyName = null,
        public ?string $leftSymbol = null,
        public ?string $rightSymbol = null,
        public ?string $decimalSymbol = null,
        public ?int    $decimalPlace = null,
        public ?string $thousandsSeparator = null,
        public ?float  $exchangedValue = null,
        public ?int    $codeiso = null,
        public ?string $lang = null,
        public ?string $langCode = null,
        public ?bool   $isActive = null,
    )
    {
    }
}
