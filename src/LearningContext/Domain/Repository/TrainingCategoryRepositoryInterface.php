<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Model\TrainingCategoryListResult;

interface TrainingCategoryRepositoryInterface
{
    public function save(TrainingCategory $category): TrainingCategory;
    public function getById(int $id): TrainingCategory;
    /**
     * @param list<int> $ids
     * @return list<TrainingCategory>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): TrainingCategoryListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countTrainingUsage(int $id): int;
    public function delete(TrainingCategory $category): void;
}
