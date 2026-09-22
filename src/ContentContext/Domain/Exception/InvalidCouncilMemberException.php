<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidCouncilMemberException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.content.invalid_council_member'; }
    public function translationDomain(): string { return 'content_context'; }
    /** @return array<string, scalar|null> */
    public function translationParameters(): array { return []; }
}
