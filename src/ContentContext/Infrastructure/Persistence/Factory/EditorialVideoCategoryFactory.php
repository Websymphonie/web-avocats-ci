<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\EditorialVideoCategory;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideoCategory\EditorialVideoCategoryEntity;

final class EditorialVideoCategoryFactory
{
    public function fromEntity(EditorialVideoCategoryEntity $entity): EditorialVideoCategory
    {
        return new EditorialVideoCategory($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getName(), $entity->getSlug(), $entity->getDescription(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }
    public function toEntity(EditorialVideoCategory $model, ?EditorialVideoCategoryEntity $entity = null): EditorialVideoCategoryEntity
    {
        $entity ??= new EditorialVideoCategoryEntity();
        return $entity->setName($model->name)->setSlug($model->slug)->setDescription($model->description);
    }
}
