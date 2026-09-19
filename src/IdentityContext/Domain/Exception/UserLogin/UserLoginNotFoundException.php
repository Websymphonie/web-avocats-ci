<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\UserLogin;

use DomainException;
use Websymphonie\IdentityContext\Domain\Exception\UserFacingError;

final class UserLoginNotFoundException extends DomainException implements UserFacingError
{
    public static function message(string $text): self
    {
        return new self($text);
    }

    public static function withId(int $id): self
    {
        return new self(sprintf('Aucune donnée ne correspond à cet identifiant %d', $id));
    }

    public static function withUuid(string $uuid): self
    {
        return new self(sprintf('Aucune donnée ne correspond à cet identifiant %s', $uuid));
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.user_not_found';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'admin_context';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}