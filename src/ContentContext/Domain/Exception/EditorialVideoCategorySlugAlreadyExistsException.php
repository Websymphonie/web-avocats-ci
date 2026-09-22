<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EditorialVideoCategorySlugAlreadyExistsException extends DomainException implements UserFacingError
{
    public static function withSlug(string $slug): self { return new self(sprintf('Le slug de catégorie vidéo « %s » est déjà utilisé.', $slug)); }
    public function translationId(): string { return 'exceptions.content.editorial_video_category_slug_exists'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
