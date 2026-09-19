<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;

final class NewsCategoryFactory
{
    public function fromEntity(NewsCategoryEntity $entity): NewsCategory
    {
        return new NewsCategory(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            name: $entity->getName(),
            slug: $entity->getSlug(),
            description: $entity->getDescription(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(NewsCategory $model, ?NewsCategoryEntity $entity = null): NewsCategoryEntity
    {
        $entity ??= new NewsCategoryEntity();
        return $entity->setName($model->name)->setSlug($model->slug)->setDescription($model->description);
    }
}
