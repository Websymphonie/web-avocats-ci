<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class DocumentPublicationNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('La publication #%d est introuvable.', $id)); }
    public static function withUuid(string $uuid): self { return new self(sprintf('La publication %s est introuvable.', $uuid)); }
    public function translationId(): string { return 'exceptions.content.document_publication_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
