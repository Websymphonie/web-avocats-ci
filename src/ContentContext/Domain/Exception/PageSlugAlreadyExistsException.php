<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class PageSlugAlreadyExistsException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.content.page_slug_already_exists'; }
    public function translationDomain(): string { return 'content_context'; }
    /** @return array<string, scalar|null> */
    public function translationParameters(): array { return []; }
}
