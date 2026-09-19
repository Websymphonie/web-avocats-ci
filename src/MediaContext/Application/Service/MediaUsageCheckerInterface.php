<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

/** Consumer-neutral port: Media never knows which business model uses an asset. */
interface MediaUsageCheckerInterface
{
    public function isUsed(int $mediaId): bool;
}
