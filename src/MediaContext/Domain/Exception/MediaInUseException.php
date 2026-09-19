<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class MediaInUseException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('Le média #%d est encore utilisé et ne peut pas être supprimé.', $id)); }
    public function translationId(): string { return 'exceptions.media.in_use'; }
    public function translationDomain(): string { return 'media_context'; }
    public function translationParameters(): array { return []; }
}
