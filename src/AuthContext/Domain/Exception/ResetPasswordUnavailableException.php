<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class ResetPasswordUnavailableException extends DomainException implements UserFacingError
{
    public function translationId(): string
    {
        return 'exceptions.reset_password_unavailable';
    }

    public function translationDomain(): string
    {
        return 'auth_context';
    }

    /** @return array<string, mixed> */
    public function translationParameters(): array
    {
        return [];
    }
}
