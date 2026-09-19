<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\DocumentPublication;
use Websymphonie\ContentContext\Domain\Model\Tag;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;

final class DocumentPublicationFactory
{
    public function __construct(private readonly TagFactory $tagFactory) {}
    public function fromEntity(DocumentPublicationEntity $entity): DocumentPublication
    {
        return new DocumentPublication($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getTitle(), $entity->getSlug(), $entity->getDescription(), $entity->getStoredFileId(), $entity->getAccessLevel(), $entity->getStatus(), $entity->getPublishedAt(), $entity->getCreatedAt(), $entity->getUpdatedAt(), array_map(fn (TagEntity $tag): Tag => $this->tagFactory->fromEntity($tag), $entity->getTags()->toArray()));
    }
    /** @param list<TagEntity> $tags */
    public function toEntity(DocumentPublication $document, ?DocumentPublicationEntity $entity = null, array $tags = []): DocumentPublicationEntity
    {
        $entity ??= new DocumentPublicationEntity();
        return $entity->setTitle($document->title)->setSlug($document->slug)->setDescription($document->description)->setStoredFileId($document->storedFileId)->setAccessLevel($document->accessLevel)->setStatus($document->status)->setPublishedAt($document->publishedAt)->replaceTags($tags);
    }
}
