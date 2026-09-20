<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\TrainingListResult;
use Websymphonie\LearningContext\Domain\Model\TrainingSummary;

interface TrainingRepositoryInterface
{
    public function save(Training $training): Training;
    public function getById(int $id): Training;
    public function getByUuid(string $uuid): Training;
    public function delete(Training $training): void;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function countMediaUsage(int $mediaId): int;

    /**
     * @param list<int> $ids
     * @return list<Training>
     */
    public function findByIds(array $ids): array;

    /**
     * @param list<int> $ids
     * @return list<TrainingSummary>
     */
    public function findSummariesByIds(array $ids): array;

    public function list(
        ?string $search,
        ?TrainingStatus $status,
        ?TrainingVisibility $visibility,
        ?TrainingAccessType $accessType,
        ?TrainingType $type,
        ?int $categoryId,
        ?int $tagId,
        int $page,
        int $limit,
    ): TrainingListResult;
}
