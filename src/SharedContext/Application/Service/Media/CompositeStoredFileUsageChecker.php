<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Media;

use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;

final readonly class CompositeStoredFileUsageChecker implements StoredFileUsageCheckerInterface
{
    /** @param iterable<StoredFileUsageCheckerInterface> $checkers */
    public function __construct(private iterable $checkers) {}

    public function isUsed(int $storedFileId): bool
    {
        foreach ($this->checkers as $checker) {
            if ($checker->isUsed($storedFileId)) {
                return true;
            }
        }
        return false;
    }
}
