<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Exception\InvalidLessonVideoException;
use Websymphonie\LearningContext\Domain\Model\Lesson;

final class LessonTest extends TestCase
{
    public function testUpdateNormalizesOptionalSummary(): void
    {
        $lesson = new Lesson(1, 'uuid', 10, 'Initial', 'Résumé', 2);

        $lesson->update('  Nouvelle leçon  ', '   ');

        self::assertSame('Nouvelle leçon', $lesson->title);
        self::assertNull($lesson->summary);
        self::assertSame(10, $lesson->moduleId);
        self::assertSame(2, $lesson->position);
    }

    public function testYouTubeVideoIsNormalizedToAnExternalReference(): void
    {
        $lesson = new Lesson(1, 'uuid', 10, 'Initial');
        $lesson->update('Leçon', null, '<p></p>', 'https://youtu.be/abcDEF_123');

        self::assertTrue($lesson->hasVideo());
        self::assertSame('abcDEF_123', $lesson->externalVideoId);
        self::assertSame('https://www.youtube-nocookie.com/embed/abcDEF_123', $lesson->videoEmbedUrl());
        self::assertFalse($lesson->hasContent());
    }

    public function testNonYouTubeVideoIsRejected(): void
    {
        $this->expectException(InvalidLessonVideoException::class);

        (new Lesson(1, 'uuid', 10, 'Initial'))->setVideo('https://vimeo.com/123456');
    }

    public function testLessonIsReadyWithMeaningfulContentOrVideoOrResource(): void
    {
        $lesson = new Lesson(1, 'uuid', 10, 'Initial');

        self::assertFalse($lesson->isReadyForPublication(0));
        $lesson->update('Leçon', null, '<p>Texte</p>');
        self::assertTrue($lesson->isReadyForPublication(0));
        $lesson->update('Leçon', null, '<p></p>');
        self::assertTrue($lesson->isReadyForPublication(1));
    }
}
