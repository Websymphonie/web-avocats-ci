<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EditorialVideoNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('La vidéo éditoriale #%d est introuvable.', $id));
    }

    public static function withSlug(string $slug): self
    {
        return new self(sprintf('La vidéo éditoriale « %s » est introuvable.', $slug));
    }
    public function translationId(): string { return 'exceptions.content.editorial_video_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
