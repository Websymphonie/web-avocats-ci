<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

final readonly class CurrencyReference
{
    public function __construct(
        public string $code,
        public string $name,
        public bool $active = true,
    ) {
    }

    public function label(): string
    {
        return $this->name !== '' ? sprintf('%s (%s)', $this->name, $this->code) : $this->code;
    }
}
