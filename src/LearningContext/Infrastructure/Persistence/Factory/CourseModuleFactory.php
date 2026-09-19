<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\CourseModule;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;

final class CourseModuleFactory
{
    public function fromEntity(CourseModuleEntity $entity): CourseModule
    {
        return new CourseModule($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getTrainingId(), $entity->getTitle(), $entity->getDescription(), $entity->getPosition(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(CourseModule $model, ?CourseModuleEntity $entity = null): CourseModuleEntity
    {
        return ($entity ?? new CourseModuleEntity())
            ->setTrainingId($model->trainingId)
            ->setTitle($model->title)
            ->setDescription($model->description)
            ->setPosition($model->position);
    }
}
