<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\LearningContext\Domain\Enum\LessonProgressStatus;

final class LessonProgress
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $enrollmentId,
        public readonly int $lessonId,
        public LessonProgressStatus $status = LessonProgressStatus::IN_PROGRESS,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $lastAccessedAt = null,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function start(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();
        $this->startedAt ??= $now;
        $this->lastAccessedAt = $now;
        if ($this->status !== LessonProgressStatus::COMPLETED) {
            $this->status = LessonProgressStatus::IN_PROGRESS;
        }
    }

    public function complete(?DateTimeImmutable $now = null): void
    {
        $now ??= new DateTimeImmutable();
        $this->startedAt ??= $now;
        $this->lastAccessedAt = $now;
        if ($this->status !== LessonProgressStatus::COMPLETED) {
            $this->status = LessonProgressStatus::COMPLETED;
            $this->completedAt = $now;
        }
    }

    public function isCompleted(): bool
    {
        return $this->status === LessonProgressStatus::COMPLETED;
    }
}
