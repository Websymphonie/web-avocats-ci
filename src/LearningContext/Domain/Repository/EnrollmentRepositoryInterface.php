<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Domain\Repository;

use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Model\EnrollmentListResult;

interface EnrollmentRepositoryInterface
{
    public function save(Enrollment $enrollment): Enrollment;
    public function getById(int $id): Enrollment;
    public function findByTrainingAndUser(int $trainingId, int $userId): ?Enrollment;
    public function getByUuid(string $uuid): Enrollment;
    public function countByTraining(int $trainingId): int;
    public function countActiveByTraining(int $trainingId): int;
    /** @param list<int>|null $userIds */
    public function listByTraining(int $trainingId, ?EnrollmentStatus $status, ?EnrollmentSource $source, ?array $userIds, int $page, int $limit): EnrollmentListResult;
}
