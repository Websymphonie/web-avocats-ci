<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Service;

use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUsageCheckerInterface;

final readonly class GalleryMediaUsageChecker implements MediaUsageCheckerInterface
{
    public function __construct(private PhotoGalleryRepositoryInterface $repository, private NewsRepositoryInterface $newsRepository, private EventRepositoryInterface $eventRepository, private PageRepositoryInterface $pageRepository) {}
    public function isUsed(int $mediaId): bool { return $this->repository->countMediaUsage($mediaId) > 0 || $this->newsRepository->countMediaUsage($mediaId) > 0 || $this->eventRepository->countMediaUsage($mediaId) > 0 || $this->pageRepository->countMediaUsage($mediaId) > 0; }
}
