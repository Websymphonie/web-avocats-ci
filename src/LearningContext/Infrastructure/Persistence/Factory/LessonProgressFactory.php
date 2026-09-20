<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\LessonProgress;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonProgress\LessonProgressEntity;

final class LessonProgressFactory
{
    public function fromEntity(LessonProgressEntity $entity): LessonProgress
    {
        return new LessonProgress($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getEnrollmentId(), $entity->getLessonId(), $entity->getStatus(), $entity->getStartedAt(), $entity->getLastAccessedAt(), $entity->getCompletedAt(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(LessonProgress $model, ?LessonProgressEntity $entity = null): LessonProgressEntity
    {
        return ($entity ?? new LessonProgressEntity())
            ->setEnrollmentId($model->enrollmentId)
            ->setLessonId($model->lessonId)
            ->setStatus($model->status)
            ->setStartedAt($model->startedAt)
            ->setLastAccessedAt($model->lastAccessedAt ?? new \DateTimeImmutable())
            ->setCompletedAt($model->completedAt);
    }
}
