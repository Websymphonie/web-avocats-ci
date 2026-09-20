<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

class PaymentException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.payment.payment_error'; }
    public function translationDomain(): string { return 'payment_context'; }
    public function translationParameters(): array { return []; }
}
