<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidMediaUploadException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.media.invalid_upload'; }
    public function translationDomain(): string { return 'media_context'; }
    public function translationParameters(): array { return []; }
}
