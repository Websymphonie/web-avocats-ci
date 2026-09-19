<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class NewsCategoryInUseException extends DomainException implements UserFacingError
{
    public static function withId(int $id, int $count): self { return new self(sprintf('Cette catégorie est utilisée par %d actualité(s) et ne peut pas être supprimée.', $count)); }
    public function translationId(): string { return 'exceptions.content.news_category_in_use'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
