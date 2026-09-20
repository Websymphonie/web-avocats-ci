<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class LessonNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('La leçon #%d est introuvable.', $id)); }
    public static function withUuid(string $uuid): self { return new self(sprintf('La leçon « %s » est introuvable.', $uuid)); }
    public function translationId(): string { return 'exceptions.learning.lesson_not_found'; }
    public function translationDomain(): string { return 'learning_context'; }
    public function translationParameters(): array { return []; }
}
