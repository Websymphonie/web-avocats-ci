<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;

final class LessonFactory
{
    public function fromEntity(LessonEntity $entity): Lesson
    {
        return new Lesson($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getModuleId(), $entity->getTitle(), $entity->getSummary(), $entity->getPosition(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(Lesson $model, ?LessonEntity $entity = null): LessonEntity
    {
        return ($entity ?? new LessonEntity())
            ->setModuleId($model->moduleId)
            ->setTitle($model->title)
            ->setSummary($model->summary)
            ->setPosition($model->position);
    }
}
