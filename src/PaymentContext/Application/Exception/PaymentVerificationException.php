<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Exception;

use RuntimeException;
use Throwable;

final class PaymentVerificationException extends RuntimeException
{
    private function __construct(string $message, public readonly bool $retryable, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function unavailable(string $message, ?Throwable $previous = null): self
    {
        return new self($message, true, $previous);
    }

    public static function invalid(string $message): self
    {
        return new self($message, false);
    }
}
