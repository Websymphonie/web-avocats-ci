<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class ContactMessageDeliveryRetryNotAllowedException extends RuntimeException implements UserFacingError
{
    public function __construct()
    {
        parent::__construct('Seuls les messages en échec peuvent être relancés.');
    }

    public function translationId(): string
    {
        return 'exceptions.contact_message_delivery_retry_not_allowed';
    }

    public function translationDomain(): string
    {
        return 'contact_context';
    }

    /** @return array<string, scalar|null> */
    public function translationParameters(): array
    {
        return [];
    }
}
