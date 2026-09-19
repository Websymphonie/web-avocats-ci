<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;

final class TrainingFactory
{
    public function fromEntity(TrainingEntity $entity): Training
    {
        return new Training(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            type: $entity->getType(),
            title: $entity->getTitle(),
            slug: $entity->getSlug(),
            summary: $entity->getSummary(),
            description: $entity->getDescription(),
            visibility: $entity->getVisibility(),
            accessType: $entity->getAccessType(),
            status: $entity->getStatus(),
            publishedAt: $entity->getPublishedAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            coverMediaId: $entity->getCoverMediaId(),
            categoryIds: array_map(static fn ($category): int => $category->getId() ?? 0, $entity->getCategories()->toArray()),
            tagIds: array_map(static fn ($tag): int => $tag->getId() ?? 0, $entity->getTags()->toArray()),
        );
    }

    /**
     * @param list<object> $categoryEntities
     * @param list<object> $tagEntities
     */
    public function toEntity(Training $model, ?TrainingEntity $entity = null, array $categoryEntities = [], array $tagEntities = []): TrainingEntity
    {
        $entity ??= new TrainingEntity();

        return $entity
            ->setType($model->type)
            ->setTitle($model->title)
            ->setSlug($model->slug)
            ->setSummary($model->summary)
            ->setDescription($model->description)
            ->setVisibility($model->visibility)
            ->setAccessType($model->accessType)
            ->setStatus($model->status)
            ->setPublishedAt($model->publishedAt)
            ->setCoverMediaId($model->coverMediaId)
            ->replaceCategories($categoryEntities)
            ->replaceTags($tagEntities);
    }
}
