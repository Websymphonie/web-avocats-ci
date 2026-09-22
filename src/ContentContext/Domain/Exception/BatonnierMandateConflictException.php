<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Throwable;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class BatonnierMandateConflictException extends RuntimeException implements UserFacingError
{
    public function __construct(string $message = 'Un mandat courant existe déjà. Terminez-le avant d’en créer ou modifier un autre.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function translationId(): string { return 'exceptions.content.batonnier_mandate_conflict'; }
    public function translationDomain(): string { return 'content_context'; }
    /** @return array<string, scalar|null> */
    public function translationParameters(): array { return []; }
}
