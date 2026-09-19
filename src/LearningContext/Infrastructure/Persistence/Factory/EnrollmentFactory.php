<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;

final class EnrollmentFactory
{
    public function fromEntity(EnrollmentEntity $entity): Enrollment
    {
        return new Enrollment($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getTrainingId(), $entity->getUserId(), $entity->getStatus(), $entity->getSource(), $entity->getActivatedAt(), $entity->getRevokedAt(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(Enrollment $model, ?EnrollmentEntity $entity = null): EnrollmentEntity
    {
        return ($entity ?? new EnrollmentEntity())->setTrainingId($model->trainingId)->setUserId($model->userId)->setStatus($model->status)->setSource($model->source)->setActivatedAt($model->activatedAt)->setRevokedAt($model->revokedAt);
    }
}
