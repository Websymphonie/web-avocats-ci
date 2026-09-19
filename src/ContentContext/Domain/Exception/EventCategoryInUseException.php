<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EventCategoryInUseException extends DomainException implements UserFacingError
{
    public static function withId(int $id, int $count): self { return new self(sprintf('La catégorie d’événement #%d est utilisée par %d événement(s) et ne peut pas être supprimée.', $id, $count)); }
    public function translationId(): string { return 'exceptions.content.event_category_in_use'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
