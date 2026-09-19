<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
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
}
