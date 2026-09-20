<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class PageNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('La page #%d est introuvable.', $id));
    }

    public function translationId(): string { return 'exceptions.content.page_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    /** @return array<string, scalar|null> */
    public function translationParameters(): array { return []; }
}
