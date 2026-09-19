<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\CourseModule;

interface CourseModuleRepositoryInterface
{
    public function save(CourseModule $module): CourseModule;
    public function getById(int $id): CourseModule;
    public function getByIdForTraining(int $id, int $trainingId): CourseModule;
    public function delete(CourseModule $module): void;

    /** @return list<CourseModule> */
    public function listByTraining(int $trainingId): array;

    /** @param list<int> $moduleIds */
    public function reorder(int $trainingId, array $moduleIds): void;

    public function countByTraining(int $trainingId): int;
    public function countEmptyByTraining(int $trainingId): int;
}
