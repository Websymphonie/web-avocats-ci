<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class LessonHasProgressException extends RuntimeException implements UserFacingError
{
    public function __construct()
    {
        parent::__construct('Cette leçon ne peut pas être supprimée car des apprenants ont déjà commencé leur progression.');
    }

    public function translationId(): string { return 'exceptions.learning.lesson_has_progress'; }
    public function translationDomain(): string { return 'learning_context'; }
    public function translationParameters(): array { return []; }
}
