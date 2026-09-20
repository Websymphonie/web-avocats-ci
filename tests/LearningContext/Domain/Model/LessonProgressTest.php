<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;
use Websymphonie\LearningContext\Domain\Model\LessonProgress;

final class LessonProgressTest extends TestCase
{
    public function testStartCreatesAnInProgressRecordAndIsIdempotentForCompletion(): void
    {
        $progress = new LessonProgress(0, 'uuid', 10, 20);
        $first = new DateTimeImmutable('2026-09-19 10:00:00');
        $second = new DateTimeImmutable('2026-09-19 10:05:00');

        $progress->start($first);
        $progress->start($second);

        self::assertSame(LessonProgressStatus::IN_PROGRESS, $progress->status);
        self::assertSame($first, $progress->startedAt);
        self::assertSame($second, $progress->lastAccessedAt);
        self::assertNull($progress->completedAt);
    }

    public function testCompleteFromAbsentProgressStartsAndCompletesTheLesson(): void
    {
        $progress = new LessonProgress(0, 'uuid', 10, 20);
        $completedAt = new DateTimeImmutable('2026-09-19 11:00:00');

        $progress->complete($completedAt);

        self::assertSame(LessonProgressStatus::COMPLETED, $progress->status);
        self::assertSame($completedAt, $progress->startedAt);
        self::assertSame($completedAt, $progress->completedAt);
        self::assertSame($completedAt, $progress->lastAccessedAt);
    }

    public function testCompletingAnAlreadyCompletedLessonPreservesTheFirstCompletionDate(): void
    {
        $progress = new LessonProgress(1, 'uuid', 10, 20);
        $firstCompletion = new DateTimeImmutable('2026-09-19 11:00:00');
        $laterAccess = new DateTimeImmutable('2026-09-19 12:00:00');

        $progress->complete($firstCompletion);
        $progress->complete($laterAccess);

        self::assertSame($firstCompletion, $progress->completedAt);
        self::assertSame($laterAccess, $progress->lastAccessedAt);
    }
}
