<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;

interface TrainingTagRepositoryInterface
{
    public function save(TrainingTag $tag): TrainingTag;
    public function getById(int $id): TrainingTag;
    /**
     * @param list<int> $ids
     * @return list<TrainingTag>
     */
    public function findByIds(array $ids): array;
    public function list(?string $search, int $page, int $limit): TrainingTagListResult;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countTrainingUsage(int $id): int;
    public function delete(TrainingTag $tag): void;
}
