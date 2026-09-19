<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EventCategorySlugAlreadyExistsException extends RuntimeException implements UserFacingError
{
    public static function withSlug(string $slug): self { return new self(sprintf('Le slug « %s » est déjà utilisé.', $slug)); }
    public function translationId(): string { return 'exceptions.content.event_category_slug_already_exists'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
