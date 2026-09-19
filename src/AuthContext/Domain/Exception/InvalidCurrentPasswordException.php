<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Domain\Exception;

use RuntimeException;
use Throwable;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

class InvalidCurrentPasswordException extends RuntimeException implements UserFacingError
{
    public function __construct(string $message = "Le mot de passe actuel n'est pas valide.", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getMessageKey(): string
    {
        return "Le mot de passe actuel n'est pas valide.";
    }

    /**
     * @return string
     */
    public function translationId(): string
    {
        return 'exceptions.passwords.password_invalid';
    }

    /**
     * @return string
     */
    public function translationDomain(): string
    {
        return 'identity_and_access';
    }

    /**
     * @return array<string, mixed>
     */
    public function translationParameters(): array
    {
        return [];
    }
}
