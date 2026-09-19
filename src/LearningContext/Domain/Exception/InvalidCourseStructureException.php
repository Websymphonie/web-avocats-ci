<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidCourseStructureException extends RuntimeException implements UserFacingError
{
    public function translationId(): string { return 'exceptions.learning.invalid_course_structure'; }
    public function translationDomain(): string { return 'learning_context'; }
    public function translationParameters(): array { return []; }
}
