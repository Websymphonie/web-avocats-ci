<?php

declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Media;

use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

/** Composes consumer-owned usage checks without making MediaContext know its consumers. */
final readonly class CompositeMediaUsageChecker implements MediaUsageCheckerInterface
{
    /** @param iterable<MediaUsageCheckerInterface> $checkers */
    public function __construct(private iterable $checkers) {}

    public function isUsed(int $mediaId): bool
    {
        foreach ($this->checkers as $checker) {
            if ($checker->isUsed($mediaId)) {
                return true;
            }
        }

        return false;
    }
}
