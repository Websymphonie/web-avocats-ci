<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class EnrollmentNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withId(int $id): self { return new self(sprintf('Inscription #%d introuvable.', $id)); }
    public static function withUuid(string $uuid): self { return new self(sprintf('Inscription %s introuvable.', $uuid)); }
    public function translationId(): string { return 'exceptions.learning.enrollment_not_found'; }
    public function translationDomain(): string { return 'learning_context'; }
    public function translationParameters(): array { return []; }
}
