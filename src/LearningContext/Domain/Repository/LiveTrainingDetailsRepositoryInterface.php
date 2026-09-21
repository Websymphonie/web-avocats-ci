<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;

interface LiveTrainingDetailsRepositoryInterface
{
    public function save(LiveTrainingDetails $details): LiveTrainingDetails;

    public function findByTrainingId(int $trainingId): ?LiveTrainingDetails;

    /**
     * @param list<int> $trainingIds
     * @return array<int, LiveTrainingDetails>
     */
    public function findByTrainingIds(array $trainingIds): array;

    public function getByTrainingId(int $trainingId): LiveTrainingDetails;

    public function deleteByTrainingId(int $trainingId): void;
}
