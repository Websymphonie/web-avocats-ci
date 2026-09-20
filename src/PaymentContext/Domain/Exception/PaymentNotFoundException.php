<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

final class PaymentNotFoundException extends PaymentException
{
    public static function withUuid(string $uuid): self { return new self(sprintf('Le paiement « %s » est introuvable.', $uuid)); }
    public function translationId(): string { return 'exceptions.payment.payment_not_found'; }
}
