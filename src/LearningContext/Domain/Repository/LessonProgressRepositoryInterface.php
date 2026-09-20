<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\CourseProgress;
use Websymphonie\LearningContext\Domain\Model\LessonProgress;

interface LessonProgressRepositoryInterface
{
    public function save(LessonProgress $progress): LessonProgress;
    public function findByEnrollmentAndLesson(int $enrollmentId, int $lessonId): ?LessonProgress;

    /** @return list<LessonProgress> */
    public function listByEnrollment(int $enrollmentId): array;

    public function countByLesson(int $lessonId): int;

    /**
     * @param list<int> $enrollmentIds
     * @return array<int, CourseProgress>
     */
    public function summarizeByEnrollmentIds(array $enrollmentIds, int $totalLessons): array;
}
