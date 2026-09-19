<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\User;

use DomainException;
use Websymphonie\IdentityContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Enum\StringEnum;

final class TokenNotFoundException extends DomainException implements UserFacingError
{
    public static function withToken(string $token): self
    {
        return new self(StringEnum::INVOICE_NOT_FOUND_ERROR_TEXT->withString($token));
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.token_not_found';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'identity_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}