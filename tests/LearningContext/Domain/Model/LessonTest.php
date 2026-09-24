<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\VideoProvider;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;
use Websymphonie\LearningContext\Domain\Model\Lesson;

final class LessonTest extends TestCase
{
    public function testUpdateNormalizesOptionalSummaryAndAllowsNoVideo(): void
    {
        $lesson = new Lesson(1, 'uuid', 10, 'Initial', 'Résumé', 2);

        $lesson->update('  Nouvelle leçon  ', '   ');

        self::assertSame('Nouvelle leçon', $lesson->title);
        self::assertNull($lesson->summary);
        self::assertNull($lesson->videoSource);
        self::assertSame(10, $lesson->moduleId);
        self::assertSame(2, $lesson->position);
        self::assertFalse($lesson->hasVideo());
    }

    public function testLessonCanHoldAProviderNeutralSource(): void
    {
        $source = new ExternalVideoSource(VideoProvider::YOUTUBE, 'abcDEF_123');
        $lesson = new Lesson(1, 'uuid', 10, 'Initial');
        $lesson->update('Leçon', null, '<p></p>', $source);

        self::assertTrue($lesson->hasVideo());
        self::assertSame($source, $lesson->videoSource);
        self::assertFalse($lesson->hasContent());
    }

    public function testLessonReadinessUsesSourceAsVideoPresence(): void
    {
        $lesson = new Lesson(1, 'uuid', 10, 'Initial');

        self::assertFalse($lesson->isReadyForPublication(0));
        $lesson->update('Leçon', null, '<p>Texte</p>');
        self::assertTrue($lesson->isReadyForPublication(0));
        $lesson->update('Leçon', null, '<p></p>', new ExternalVideoSource(VideoProvider::MUX, 'mux-playback-id'));
        self::assertTrue($lesson->isReadyForPublication(0));
    }
}
