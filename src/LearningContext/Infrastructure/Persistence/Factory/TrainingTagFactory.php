<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;

final class TrainingTagFactory
{
    public function fromEntity(TrainingTagEntity $entity): TrainingTag { return new TrainingTag($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getName(), $entity->getSlug(), $entity->getCreatedAt(), $entity->getUpdatedAt()); }
    public function toEntity(TrainingTag $model, ?TrainingTagEntity $entity = null): TrainingTagEntity { return ($entity ?? new TrainingTagEntity())->setName($model->name)->setSlug($model->slug); }
}
