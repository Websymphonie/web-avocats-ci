<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;

final readonly class PhotoGalleryUsageChecker
{
    public function __construct(private NewsRepositoryInterface $newsRepository, private EventRepositoryInterface $eventRepository) {}

    public function isUsed(int $galleryId): bool
    {
        return $this->newsRepository->countPhotoGalleryUsage($galleryId) > 0 || $this->eventRepository->countPhotoGalleryUsage($galleryId) > 0;
    }
}
