<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\PagePersonGroupRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

final readonly class PagePersonMediaUsageChecker implements MediaUsageCheckerInterface
{
    public function __construct(private PagePersonGroupRepositoryInterface $repository)
    {
    }

    public function isUsed(int $mediaId): bool
    {
        return $this->repository->countMediaUsage($mediaId) > 0;
    }
}
