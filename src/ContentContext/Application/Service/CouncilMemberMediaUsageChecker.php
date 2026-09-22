<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

final readonly class CouncilMemberMediaUsageChecker implements MediaUsageCheckerInterface
{
    public function __construct(private CouncilMemberRepositoryInterface $repository)
    {
    }

    public function isUsed(int $mediaId): bool
    {
        return $this->repository->countMediaUsage($mediaId) > 0;
    }
}
