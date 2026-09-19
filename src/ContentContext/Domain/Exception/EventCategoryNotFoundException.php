<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EventCategoryNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('La catégorie d’événement #%d est introuvable.', $id)); }
    public function translationId(): string { return 'exceptions.content.event_category_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
