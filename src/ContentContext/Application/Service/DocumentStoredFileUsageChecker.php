<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\DocumentPublicationRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\StoredFileUsageCheckerInterface;

final readonly class DocumentStoredFileUsageChecker implements StoredFileUsageCheckerInterface
{
    public function __construct(private DocumentPublicationRepositoryInterface $repository) {}
    public function isUsed(int $storedFileId): bool { return $this->repository->countStoredFileUsage($storedFileId) > 0; }
}
