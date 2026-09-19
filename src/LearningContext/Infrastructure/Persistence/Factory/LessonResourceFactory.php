<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\LessonResource;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LessonResource\LessonResourceEntity;
use Websymphonie\MediaContext\Domain\Model\StoredFile;

final class LessonResourceFactory
{
    public function fromEntity(LessonResourceEntity $entity, ?StoredFile $file = null): LessonResource
    {
        return new LessonResource($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getLessonId(), $entity->getStoredFileId(), $entity->getTitle(), $entity->getPosition(), $entity->getCreatedAt(), $entity->getUpdatedAt(), $file !== null ? $file->originalName : '', $file !== null ? $file->mimeType : '', $file !== null ? $file->size : 0);
    }

    public function toEntity(LessonResource $resource, ?LessonResourceEntity $entity = null): LessonResourceEntity
    {
        return ($entity ?? new LessonResourceEntity())->setLessonId($resource->lessonId)->setStoredFileId($resource->storedFileId)->setTitle($resource->title)->setPosition($resource->position);
    }
}
