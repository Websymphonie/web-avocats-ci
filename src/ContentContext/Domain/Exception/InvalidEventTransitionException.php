<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidEventTransitionException extends DomainException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.content.invalid_event_transition'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
