<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\News;
use Websymphonie\ContentContext\Domain\Model\NewsCategory;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;

final class NewsFactory
{
    public function __construct(private readonly NewsCategoryFactory $categoryFactory, private readonly TagFactory $tagFactory) {}

    public function fromEntity(NewsEntity $entity): News
    {
        return new News(
            id: $entity->getId(),
            uuid: $entity->getUuidAsString() ?? '',
            title: $entity->getTitle(),
            slug: $entity->getSlug(),
            excerpt: $entity->getExcerpt(),
            body: $entity->getBody(),
            status: $entity->getStatus(),
            publishedAt: $entity->getPublishedAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            categories: array_map(fn ($category): NewsCategory => $this->categoryFactory->fromEntity($category), $entity->getCategories()->toArray()),
            tags: array_map(fn ($tag): Tag => $this->tagFactory->fromEntity($tag), $entity->getTags()->toArray()),
            coverMediaId: $entity->getCoverMediaId(),
            photoGalleryId: $entity->getPhotoGalleryId(),
        );
    }

    /**
     * @param list<NewsCategoryEntity> $categoryEntities
     * @param list<TagEntity> $tagEntities
     */
    public function toEntity(News $model, ?NewsEntity $entity = null, array $categoryEntities = [], array $tagEntities = []): NewsEntity
    {
        $entity ??= new NewsEntity();
        $entity->setTitle($model->title)
            ->setSlug($model->slug)
            ->setExcerpt($model->excerpt)
            ->setBody($model->body)
            ->setStatus($model->status)
            ->setPublishedAt($model->publishedAt)
            ->setCoverMediaId($model->coverMediaId)
            ->setPhotoGalleryId($model->photoGalleryId)
            ->replaceCategories($categoryEntities)
            ->replaceTags($tagEntities);

        return $entity;
    }
}
