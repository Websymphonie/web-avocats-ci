<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Application\Service;

interface StoredFileUsageCheckerInterface
{
    public function isUsed(int $storedFileId): bool;
}
