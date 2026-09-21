<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class NewsNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('L’actualité #%d est introuvable.', $id));
    }

    public static function withSlug(string $slug): self
    {
        return new self(sprintf('L’actualité « %s » est introuvable.', $slug));
    }

    public function translationId(): string { return 'exceptions.content.news_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
