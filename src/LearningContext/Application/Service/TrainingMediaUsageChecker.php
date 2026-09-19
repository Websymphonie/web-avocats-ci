<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

final readonly class TrainingMediaUsageChecker implements MediaUsageCheckerInterface
{
    public function __construct(private TrainingRepositoryInterface $repository) {}

    public function isUsed(int $mediaId): bool
    {
        return $this->repository->countMediaUsage($mediaId) > 0;
    }
}
