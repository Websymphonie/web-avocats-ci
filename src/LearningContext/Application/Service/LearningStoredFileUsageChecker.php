<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Repository\LessonResourceRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;

final readonly class LearningStoredFileUsageChecker implements StoredFileUsageCheckerInterface
{
    public function __construct(private LessonResourceRepositoryInterface $repository) {}

    public function isUsed(int $storedFileId): bool
    {
        return $this->repository->countStoredFileUsage($storedFileId) > 0;
    }
}
