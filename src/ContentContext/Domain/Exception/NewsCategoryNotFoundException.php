<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class NewsCategoryNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('La catégorie d’actualité #%d est introuvable.', $id)); }
    public function translationId(): string { return 'exceptions.content.news_category_not_found'; }
    public function translationDomain(): string { return 'content_context'; }
    public function translationParameters(): array { return []; }
}
