<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Domain\Exception\User;

use DomainException;
use Websymphonie\IdentityContext\Domain\Exception\UserFacingError;
use Websymphonie\SharedContext\Domain\Enum\StringEnum;

class EmailAlreadyExistsException extends DomainException implements UserFacingError
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf(StringEnum::EMAIL_EXIST_ERROR_TEXT->value, $email));
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.users.email_allready_exists';
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