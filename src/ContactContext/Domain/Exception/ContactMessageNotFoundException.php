<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class ContactMessageNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withUuid(string $uuid): self
    {
        return new self(sprintf('Le message de contact %s est introuvable.', $uuid));
    }

    public function translationId(): string
    {
        return 'exceptions.contact.message_not_found';
    }

    public function translationDomain(): string
    {
        return 'contact_context';
    }

    /** @return array<string, scalar> */
    public function translationParameters(): array
    {
        return [];
    }
}
