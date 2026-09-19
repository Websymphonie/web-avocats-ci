<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\Event;
use Websymphonie\ContentContext\Domain\Model\EventCategory;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;

final class EventFactory
{
    public function __construct(private readonly EventCategoryFactory $categoryFactory, private readonly TagFactory $tagFactory) {}

    public function fromEntity(EventEntity $entity): Event
    {
        return new Event(
            id: $entity->getId() ?? 0, uuid: $entity->getUuidAsString() ?? '', title: $entity->getTitle(), slug: $entity->getSlug(), excerpt: $entity->getExcerpt(), description: $entity->getDescription(), format: $entity->getFormat(), startsAt: $entity->getStartsAt(), endsAt: $entity->getEndsAt(), venueName: $entity->getVenueName(), address: $entity->getAddress(), onlineUrl: $entity->getOnlineUrl(), status: $entity->getStatus(), publishedAt: $entity->getPublishedAt(), createdAt: $entity->getCreatedAt(), updatedAt: $entity->getUpdatedAt(),
            categories: array_map(fn (EventCategoryEntity $category): EventCategory => $this->categoryFactory->fromEntity($category), $entity->getCategories()->toArray()),
            tags: array_map(fn (TagEntity $tag): Tag => $this->tagFactory->fromEntity($tag), $entity->getTags()->toArray()),
            coverMediaId: $entity->getCoverMediaId(),
            photoGalleryId: $entity->getPhotoGalleryId(),
        );
    }

    /**
     * @param list<EventCategoryEntity> $categoryEntities
     * @param list<TagEntity> $tagEntities
     */
    public function toEntity(Event $model, ?EventEntity $entity = null, array $categoryEntities = [], array $tagEntities = []): EventEntity
    {
        $entity ??= new EventEntity();
        return $entity->setTitle($model->title)->setSlug($model->slug)->setExcerpt($model->excerpt)->setDescription($model->description)->setFormat($model->format)->setStartsAt($model->startsAt)->setEndsAt($model->endsAt)->setVenueName($model->venueName)->setAddress($model->address)->setOnlineUrl($model->onlineUrl)->setStatus($model->status)->setPublishedAt($model->publishedAt)->setCoverMediaId($model->coverMediaId)->setPhotoGalleryId($model->photoGalleryId)->replaceCategories($categoryEntities)->replaceTags($tagEntities);
    }
}
