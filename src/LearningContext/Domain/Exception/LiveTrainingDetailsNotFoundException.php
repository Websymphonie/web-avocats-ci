<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Exception;

use RuntimeException;
use Websymphonie\SharedContext\Domain\Exception\UserFacingError;

final class LiveTrainingDetailsNotFoundException extends RuntimeException implements UserFacingError
{
    public static function withTrainingId(int $trainingId): self
    {
        return new self(sprintf('Les détails LIVE de la formation %d sont introuvables.', $trainingId));
    }

    public function translationId(): string
    {
        return 'exceptions.learning.live_training_details_not_found';
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
