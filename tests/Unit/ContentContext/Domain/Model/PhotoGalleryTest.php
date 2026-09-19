<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\PhotoGalleryStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidPhotoGalleryException;
use Websymphonie\ContentContext\Domain\Model\PhotoGallery;

final class PhotoGalleryTest extends TestCase
{
    public function testNewGalleryIsDraftAndCannotBePublishedEmpty(): void
    {
        $gallery = new PhotoGallery(1, 'gallery', 'Galerie', 'galerie');
        self::assertSame(PhotoGalleryStatus::DRAFT, $gallery->status);
        $this->expectException(InvalidPhotoGalleryException::class);
        $gallery->publish();
    }

    public function testPublicationRequiresCoverAndAlternativeText(): void
    {
        $gallery = new PhotoGallery(1, 'gallery', 'Galerie', 'galerie');
        $gallery->addMedia([10, 11]);
        $this->expectException(InvalidPhotoGalleryException::class);
        $gallery->publish();
    }

    public function testPublicationSetsPublicationDateAfterCoverAndAltTexts(): void
    {
        $gallery = new PhotoGallery(1, 'gallery', 'Galerie', 'galerie');
        $gallery->addMedia([10, 11]);
        $gallery->setCover(10);
        $gallery->updateItemMetadata(10, 'Salle de conférence', 'Ouverture');
        $gallery->updateItemMetadata(11, 'Intervenante au pupitre', null);
        $gallery->publish();
        self::assertSame(PhotoGalleryStatus::PUBLISHED, $gallery->status);
        self::assertNotNull($gallery->publishedAt);
    }

    public function testReorderRequiresEveryGalleryItemExactlyOnce(): void
    {
        $gallery = new PhotoGallery(1, 'gallery', 'Galerie', 'galerie');
        $gallery->addMedia([10, 11]);
        $gallery->reorderItems([11, 10]);
        self::assertSame([11, 10], array_map(static fn ($item): int => $item->mediaId, $gallery->items));
        $this->expectException(InvalidPhotoGalleryException::class);
        $gallery->reorderItems([11, 11]);
    }

    public function testRemovingCoverClearsItAndNormalizesPositions(): void
    {
        $gallery = new PhotoGallery(1, 'gallery', 'Galerie', 'galerie');
        $gallery->addMedia([10, 11]); $gallery->setCover(10); $gallery->removeItem(10);
        self::assertNull($gallery->coverMediaId);
        self::assertSame(1, $gallery->items[0]->position);
    }
}
