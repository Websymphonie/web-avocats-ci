<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use DomainException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class InvalidLiveTrainingDetailsException extends DomainException implements UserFacingError
{
    public function translationId(): string
    {
        return 'exceptions.learning.invalid_live_training_details';
    }

    public function translationDomain(): string
    {
        return 'learning_context';
    }

    public function translationParameters(): array
    {
        return [];
    }
}
