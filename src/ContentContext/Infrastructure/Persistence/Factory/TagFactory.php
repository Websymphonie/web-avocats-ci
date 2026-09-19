<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;

final class TagFactory
{
    public function fromEntity(TagEntity $entity): Tag
    {
        return new Tag(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            name: $entity->getName(),
            slug: $entity->getSlug(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(Tag $model, ?TagEntity $entity = null): TagEntity
    {
        $entity ??= new TagEntity();
        return $entity->setName($model->name)->setSlug($model->slug);
    }
}
