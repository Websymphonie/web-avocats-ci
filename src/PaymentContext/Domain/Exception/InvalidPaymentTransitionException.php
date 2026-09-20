<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

final class InvalidPaymentTransitionException extends PaymentException
{
    public function translationId(): string { return 'exceptions.payment.invalid_payment_transition'; }
}
