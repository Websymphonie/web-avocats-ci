<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Application\Service;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Application\Service\PhotoGalleryUsageChecker;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;

final class PhotoGalleryUsageCheckerTest extends TestCase
{
    public function testGalleryUsedByNewsCannotBeDeleted(): void
    {
        $news = $this->createMock(NewsRepositoryInterface::class);
        $news->expects(self::once())->method('countPhotoGalleryUsage')->with(4)->willReturn(1);
        $event = $this->createStub(EventRepositoryInterface::class);

        self::assertTrue((new PhotoGalleryUsageChecker($news, $event))->isUsed(4));
    }

    public function testUnusedGalleryCanBeDeleted(): void
    {
        $news = $this->createStub(NewsRepositoryInterface::class);
        $news->method('countPhotoGalleryUsage')->willReturn(0);
        $event = $this->createStub(EventRepositoryInterface::class);
        $event->method('countPhotoGalleryUsage')->willReturn(0);

        self::assertFalse((new PhotoGalleryUsageChecker($news, $event))->isUsed(4));
    }
}
