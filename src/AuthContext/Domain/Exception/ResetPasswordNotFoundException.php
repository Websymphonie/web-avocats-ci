<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class ResetPasswordNotFoundException extends DomainException implements UserFacingError
{
    public static function message(string $text): self
    {
        return new self($text);
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.reset_password_not_found';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'auth_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}
