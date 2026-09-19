<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;

final class EventCategoryFactory
{
    public function fromEntity(EventCategoryEntity $entity): EventCategory
    {
        return new EventCategory($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getName(), $entity->getSlug(), $entity->getDescription(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(EventCategory $model, ?EventCategoryEntity $entity = null): EventCategoryEntity
    {
        $entity ??= new EventCategoryEntity();
        return $entity->setName($model->name)->setSlug($model->slug)->setDescription($model->description);
    }
}
