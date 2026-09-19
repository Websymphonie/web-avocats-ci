<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\EditorialVideo;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;

final class EditorialVideoFactory
{
    public function __construct(private readonly TagFactory $tagFactory) {}
    public function fromEntity(EditorialVideoEntity $entity): EditorialVideo
    {
        return new EditorialVideo(id: $entity->getId() ?? 0, uuid: $entity->getUuidAsString() ?? '', title: $entity->getTitle(), slug: $entity->getSlug(), excerpt: $entity->getExcerpt(), description: $entity->getDescription(), provider: $entity->getProvider(), videoUrl: $entity->getVideoUrl(), externalVideoId: $entity->getExternalVideoId(), status: $entity->getStatus(), publishedAt: $entity->getPublishedAt(), createdAt: $entity->getCreatedAt(), updatedAt: $entity->getUpdatedAt(), tags: array_map(fn (TagEntity $tag): Tag => $this->tagFactory->fromEntity($tag), $entity->getTags()->toArray()));
    }
    /** @param list<TagEntity> $tagEntities */
    public function toEntity(EditorialVideo $model, ?EditorialVideoEntity $entity = null, array $tagEntities = []): EditorialVideoEntity
    {
        $entity ??= new EditorialVideoEntity();
        return $entity->setTitle($model->title)->setSlug($model->slug)->setExcerpt($model->excerpt)->setDescription($model->description)->setProvider($model->provider)->setVideoUrl($model->videoUrl)->setExternalVideoId($model->externalVideoId)->setStatus($model->status)->setPublishedAt($model->publishedAt)->replaceTags($tagEntities);
    }
}
