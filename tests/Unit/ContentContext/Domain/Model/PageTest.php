<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Unit\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
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
        self::assertNull($page->coverMediaId);
        self::assertNull($page->group);
        self::assertSame(0, $page->sortOrder);
    }

    public function testCoverIsOptionalScalarReference(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>', coverMediaId: 12);
        self::assertSame(12, $page->coverMediaId);
        $page->setCoverMedia(null);
        self::assertNull($page->coverMediaId);
    }

    public function testEditorialGroupIsOptionalAndMutable(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>', group: PageGroup::LEGAL);
        self::assertSame(PageGroup::LEGAL, $page->group);
        $page->setGroup(PageGroup::BAR);
        self::assertSame(PageGroup::BAR, $page->group);
        $page->setGroup(null);
        self::assertNull($page->group);
    }

    public function testSortOrderIsMutableAndCannotBeNegative(): void
    {
        $page = new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>', sortOrder: 10);
        self::assertSame(10, $page->sortOrder);
        $page->setSortOrder(20);
        self::assertSame(20, $page->sortOrder);

        $this->expectException(InvalidPageException::class);
        $page->setSortOrder(-1);
    }

    public function testNegativeSortOrderIsRejectedAtConstruction(): void
    {
        $this->expectException(InvalidPageException::class);
        new Page(1, 'page-uuid', 'À propos', 'a-propos', '<p>Contenu</p>', sortOrder: -1);
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
