<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\PhotoGallery;
use Websymphonie\ContentContext\Domain\Model\PhotoGalleryItem;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery\PhotoGalleryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\PhotoGallery\PhotoGalleryItemEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;

final class PhotoGalleryFactory
{
    public function __construct(private readonly TagFactory $tagFactory) {}

    public function fromEntity(PhotoGalleryEntity $entity): PhotoGallery
    {
        return new PhotoGallery($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getTitle(), $entity->getSlug(), $entity->getDescription(), $entity->getStatus(), $entity->getPublishedAt(), $entity->getCoverMediaId(), $entity->getCreatedAt(), $entity->getUpdatedAt(), array_map(fn (TagEntity $tag): Tag => $this->tagFactory->fromEntity($tag), $entity->getTags()->toArray()), array_map(static fn (PhotoGalleryItemEntity $item): PhotoGalleryItem => new PhotoGalleryItem($item->getId() ?? 0, $item->getMediaId(), $item->getPosition(), $item->getAltText(), $item->getCaption()), $entity->getItems()->toArray()));
    }

    /** @param list<TagEntity> $tagEntities */
    public function toEntity(PhotoGallery $gallery, ?PhotoGalleryEntity $entity = null, array $tagEntities = []): PhotoGalleryEntity
    {
        $entity ??= new PhotoGalleryEntity();
        $existing = [];
        foreach ($entity->getItems() as $item) { $existing[$item->getMediaId()] = $item; }
        $itemEntities = [];
        foreach ($gallery->items as $item) {
            $itemEntity = $existing[$item->mediaId] ?? new PhotoGalleryItemEntity();
            $itemEntities[] = $itemEntity->setMediaId($item->mediaId)->setPosition($item->position)->setAltText($item->altText)->setCaption($item->caption);
        }
        return $entity->setTitle($gallery->title)->setSlug($gallery->slug)->setDescription($gallery->description)->setStatus($gallery->status)->setPublishedAt($gallery->publishedAt)->setCoverMediaId($gallery->coverMediaId)->replaceTags($tagEntities)->replaceItems($itemEntities);
    }
}
