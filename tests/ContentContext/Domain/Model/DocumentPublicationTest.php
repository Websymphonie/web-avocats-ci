<?php

declare(strict_types=1);

namespace Websymphonie\Tests\ContentContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Domain\Exception\InvalidDocumentPublicationException;
use Websymphonie\ContentContext\Domain\Model\DocumentPublication;

final class DocumentPublicationTest extends TestCase
{
    public function testPublicationStartsAsDraft(): void
    {
        $document = new DocumentPublication(0, '', 'Guide', 'guide', '', 1);
        self::assertSame(DocumentStatus::DRAFT, $document->status);
        self::assertNull($document->publishedAt);
    }

    public function testCannotPublishWithoutAStoredFile(): void
    {
        $this->expectException(InvalidDocumentPublicationException::class);
        (new DocumentPublication(0, '', 'Guide', 'guide', '', 0))->publish();
    }

    public function testPublishSetsDateOnlyOnceAndArchiveIsExplicit(): void
    {
        $document = new DocumentPublication(0, '', 'Guide', 'guide', '', 1, DocumentAccessLevel::MEMBER);
        $document->publish();
        self::assertSame(DocumentStatus::PUBLISHED, $document->status);
        self::assertNotNull($document->publishedAt);
        $publishedAt = $document->publishedAt;
        $document->archive();
        self::assertSame(DocumentStatus::ARCHIVED, $document->status);
        self::assertSame($publishedAt, $document->publishedAt);
    }
}
