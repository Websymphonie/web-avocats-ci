<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class TagNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('Le tag #%d est introuvable.', $id)); }
    public function translationId(): string { return 'exceptions.content.tag_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
