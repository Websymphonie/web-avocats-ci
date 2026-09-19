<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Exception\InvalidCourseStructureException;
use Websymphonie\LearningContext\Domain\Model\LessonResource;

final class LessonResourceTest extends TestCase
{
    public function testRenameTrimsTitle(): void
    {
        $resource = new LessonResource(1, 'uuid', 10, 42, 'Document', 1);
        $resource->rename('  Support de cours  ');

        self::assertSame('Support de cours', $resource->title);
    }

    public function testRenameRejectsAnEmptyTitle(): void
    {
        $this->expectException(InvalidCourseStructureException::class);

        (new LessonResource(1, 'uuid', 10, 42, 'Document', 1))->rename('   ');
    }
}
