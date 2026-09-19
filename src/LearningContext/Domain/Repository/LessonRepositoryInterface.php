<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\Lesson;

interface LessonRepositoryInterface
{
    public function save(Lesson $lesson): Lesson;
    public function getById(int $id): Lesson;
    public function getByIdForModule(int $id, int $moduleId): Lesson;
    public function delete(Lesson $lesson): void;

    /** @return list<Lesson> */
    public function listByModule(int $moduleId): array;

    /** @param list<int> $lessonIds */
    public function reorder(int $moduleId, array $lessonIds): void;

    public function countByModule(int $moduleId): int;
}
