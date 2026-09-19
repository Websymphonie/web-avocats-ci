<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Factory;

use Websymphonie\MediaContext\Domain\Model\Media;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;

final class MediaFactory
{
    public function fromEntity(MediaEntity $entity): Media
    {
        return new Media($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getOriginalName(), $entity->getStorageName(), $entity->getMimeType(), $entity->getSize(), $entity->getWidth(), $entity->getHeight(), $entity->getStoragePath(), $entity->getCreatedAt());
    }

    public function toEntity(Media $media, ?MediaEntity $entity = null): MediaEntity
    {
        $entity ??= new MediaEntity();
        return $entity->setOriginalName($media->originalName)->setStorageName($media->storageName)->setMimeType($media->mimeType)->setSize($media->size)->setWidth($media->width)->setHeight($media->height)->setStoragePath($media->storagePath);
    }
}
