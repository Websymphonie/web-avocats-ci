<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\User;

use DomainException;
use Websymphonie\IdentityContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Enum\StringEnum;

class PasswordNotIdenticalException extends DomainException implements UserFacingError
{
    public function __construct(string $message = StringEnum::PASSWORD_NOT_IDENTICAL_ERROR_TEXT->value)
    {
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.passwords.not_identical';
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