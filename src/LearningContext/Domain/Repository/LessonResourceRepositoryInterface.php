<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\LessonResource;

interface LessonResourceRepositoryInterface
{
    public function save(LessonResource $resource): LessonResource;
    public function getById(int $id): LessonResource;
    public function getByUuid(string $uuid): LessonResource;
    public function getByIdForLesson(int $id, int $lessonId): LessonResource;
    public function delete(LessonResource $resource): void;

    /** @return list<LessonResource> */
    public function listByLesson(int $lessonId): array;

    /** @param list<int> $resourceIds */
    public function reorder(int $lessonId, array $resourceIds): void;
    public function countByLesson(int $lessonId): int;
    public function countStoredFileUsage(int $storedFileId): int;
}
