<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\User;

use DomainException;
use Websymphonie\IdentityContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Enum\StringEnum;

final class UserNotFoundException extends DomainException implements UserFacingError
{
    public static function message(string $text): self
    {
        return new self($text);
    }

    public static function withEmail(string $email): self
    {

        return new self(StringEnum::STRING_NOT_FOUND_ERROR_TEXT->withString($email));
    }

    public static function withToken(string $token): self
    {
        return new self(StringEnum::STRING_NOT_FOUND_ERROR_TEXT->withString($token));
    }

    public static function withTokenExpired(string $token): self
    {
        return new self(StringEnum::EXPIRED_TOKEN_ERROR_TEXT->withString($token));
    }

    public static function withId(int $id): self
    {
        return new self(StringEnum::USER_NOT_FOUND_ERROR_TEXT->with($id));
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