<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidPhotoGalleryException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.content.invalid_photo_gallery'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
