<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class PhotoGallerySlugAlreadyExistsException extends RuntimeException implements UserFacingError
{
    public static function withSlug(string $slug): self { return new self(sprintf('Le slug de galerie « %s » est déjà utilisé.', $slug)); }
    public function translationId(): string { return 'exceptions.content.photo_gallery_slug_exists'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
