<?php

declare(strict_types=1);

namespace Websymphonie\MediaContext\Infrastructure\Persistence\Factory;

use Websymphonie\MediaContext\Domain\Model\StoredFile;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;

final class StoredFileFactory
{
    public function fromEntity(StoredFileEntity $entity): StoredFile
    {
        return new StoredFile($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getOriginalName(), $entity->getStorageName(), $entity->getMimeType(), $entity->getSize(), $entity->getChecksum(), $entity->getCreatedAt());
    }

    public function toEntity(StoredFile $file, ?StoredFileEntity $entity = null): StoredFileEntity
    {
        $entity ??= new StoredFileEntity();
        return $entity->setOriginalName($file->originalName)->setStorageName($file->storageName)->setMimeType($file->mimeType)->setSize($file->size)->setChecksum($file->checksum);
    }
}
