<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Application\Service;

use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\LawyerProfile\LawyerProfileRepository;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

final readonly class LawyerProfileMediaUsageChecker implements MediaUsageCheckerInterface
{
    public function __construct(private LawyerProfileRepository $profiles)
    {
    }

    public function isUsed(int $mediaId): bool
    {
        return $this->profiles->countPortraitMediaUsage($mediaId) > 0;
    }
}
