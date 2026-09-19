<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class TagInUseException extends DomainException implements UserFacingError
{
    public static function withId(int $id, int $count): self { return new self(sprintf('Ce tag est utilisé par %d contenu(s) et ne peut pas être supprimé.', $count)); }
    public function translationId(): string { return 'exceptions.content.tag_in_use'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
