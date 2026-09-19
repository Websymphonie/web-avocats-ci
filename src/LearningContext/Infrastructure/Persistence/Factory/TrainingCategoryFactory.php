<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;

final class TrainingCategoryFactory
{
    public function fromEntity(TrainingCategoryEntity $entity): TrainingCategory { return new TrainingCategory($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getName(), $entity->getSlug(), $entity->getCreatedAt(), $entity->getUpdatedAt()); }
    public function toEntity(TrainingCategory $model, ?TrainingCategoryEntity $entity = null): TrainingCategoryEntity { return ($entity ?? new TrainingCategoryEntity())->setName($model->name)->setSlug($model->slug); }
}
