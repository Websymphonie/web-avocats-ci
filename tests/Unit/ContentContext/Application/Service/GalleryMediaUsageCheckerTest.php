<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Service\GalleryMediaUsageChecker;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PageRepositoryInterface;

final class GalleryMediaUsageCheckerTest extends TestCase
{
    public function testCoverMediaUsedByNewsCannotBeDeleted(): void
    {
        $gallery = $this->createStub(PhotoGalleryRepositoryInterface::class);
        $gallery->method('countMediaUsage')->willReturn(0);
        $news = $this->createMock(NewsRepositoryInterface::class);
        $news->expects(self::once())->method('countMediaUsage')->with(12)->willReturn(1);
        $event = $this->createStub(EventRepositoryInterface::class);
        $page = $this->createStub(PageRepositoryInterface::class);

        self::assertTrue((new GalleryMediaUsageChecker($gallery, $news, $event, $page))->isUsed(12));
    }

    public function testMediaUsedOnlyByGalleryKeepsExistingBehavior(): void
    {
        $gallery = $this->createStub(PhotoGalleryRepositoryInterface::class);
        $gallery->method('countMediaUsage')->willReturn(1);
        $news = $this->createStub(NewsRepositoryInterface::class);
        $event = $this->createStub(EventRepositoryInterface::class);
        $page = $this->createStub(PageRepositoryInterface::class);

        self::assertTrue((new GalleryMediaUsageChecker($gallery, $news, $event, $page))->isUsed(12));
    }

    public function testCoverMediaUsedOnlyByPageCannotBeDeleted(): void
    {
        $gallery = $this->createStub(PhotoGalleryRepositoryInterface::class);
        $news = $this->createStub(NewsRepositoryInterface::class);
        $event = $this->createStub(EventRepositoryInterface::class);
        $page = $this->createStub(PageRepositoryInterface::class);
        $page->method('countMediaUsage')->willReturn(1);

        self::assertTrue((new GalleryMediaUsageChecker($gallery, $news, $event, $page))->isUsed(12));
    }
}
