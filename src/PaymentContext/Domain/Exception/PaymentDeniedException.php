<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

final class PaymentDeniedException extends PaymentException
{
    public function __construct(string $message = 'Cette formation ne peut pas être achetée actuellement.') { parent::__construct($message); }
    public function translationId(): string { return 'exceptions.payment.payment_denied'; }
}
