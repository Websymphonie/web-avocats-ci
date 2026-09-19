<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidDocumentPublicationException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.content.invalid_document_publication'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
