<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidPageException;
use Websymphonie\ContentContext\Domain\Exception\InvalidPageTransitionException;
use Websymphonie\ContentContext\Domain\Model\Page;

final class PageTest extends TestCase
{
    public function testNewPageIsDraft(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '');
        self::assertSame(PageStatus::DRAFT, $page->status);
        self::assertNull($page->publishedAt);
    }

    public function testCannotPublishWithoutContent(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p></p>');
        $this->expectException(InvalidPageException::class);
        $page->publish();
    }

    public function testPublishUnpublishAndRepublishPreservesOriginalPublicationDate(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>');
        $page->publish();
        $publishedAt = $page->publishedAt;
        $page->unpublish();
        self::assertSame(PageStatus::DRAFT, $page->status);
        $page->publish();
        self::assertSame(PageStatus::PUBLISHED, $page->status);
        self::assertSame($publishedAt, $page->publishedAt);
    }

    public function testUnpublishRequiresPublishedPage(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>');
        $this->expectException(InvalidPageTransitionException::class);
        $page->unpublish();
    }

    public function testTitleAndSlugAreRequired(): void
    {
        $this->expectException(InvalidPageException::class);
        new Page(1, 'page-uuid', '', 'a-propos', 'Contenu');
    }
}
