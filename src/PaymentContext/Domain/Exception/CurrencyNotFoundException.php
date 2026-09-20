<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

final class CurrencyNotFoundException extends PaymentException
{
    public static function withCode(string $code): self
    {
        return new self(sprintf('La devise active « %s » n’existe pas dans le référentiel.', strtoupper(trim($code))));
    }

    public function translationId(): string
    {
        return 'exceptions.payment.currency_not_found';
    }
}
