<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidNewsTransitionException;
use Websymphonie\ContentContext\Domain\Model\News;

final class NewsTest extends TestCase
{
    public function testNewNewsStartsAsDraft(): void
    {
        $news = new News(0, '', 'Titre', 'titre', null, 'Contenu');
        self::assertSame(NewsStatus::DRAFT, $news->status);
        self::assertNull($news->publishedAt);
    }

    public function testPublishingSetsPublishedAtOnlyOnce(): void
    {
        $news = new News(0, '', 'Titre', 'titre', null, 'Contenu');
        $news->publish();
        $publishedAt = $news->publishedAt;
        self::assertSame(NewsStatus::PUBLISHED, $news->status);
        self::assertNotNull($publishedAt);
        $this->expectException(InvalidNewsTransitionException::class);
        $news->publish();
    }

    public function testArchiveIsOnlyAvailableAfterPublication(): void
    {
        $news = new News(0, '', 'Titre', 'titre', null, 'Contenu');
        $this->expectException(InvalidNewsTransitionException::class);
        $news->archive();
    }

    public function testPublishedSlugRemainsStableWhenTitleChanges(): void
    {
        $news = new News(4, 'uuid', 'Titre', 'titre-stable', null, 'Contenu');
        $news->publish();
        $news->update('Nouveau titre', 'nouveau-titre', null, 'Nouveau contenu');
        self::assertSame('titre-stable', $news->slug);
    }

    public function testCoverAndGalleryAreOptionalScalarReferences(): void
    {
        $news = new News(1, 'uuid', 'Titre', 'titre', null, 'Contenu');
        $news->setCoverMedia(12);
        $news->setPhotoGallery(7);
        self::assertSame(12, $news->coverMediaId);
        self::assertSame(7, $news->photoGalleryId);
        $news->setCoverMedia(null);
        $news->setPhotoGallery(null);
        self::assertNull($news->coverMediaId);
        self::assertNull($news->photoGalleryId);
    }
}
