<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Enum\LearningVideoProvider;
use Websymphonie\LearningContext\Domain\Model\Lesson;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;

final class LessonFactory
{
    public function fromEntity(LessonEntity $entity): Lesson
    {
        return new Lesson(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            moduleId: $entity->getModuleId(),
            title: $entity->getTitle(),
            summary: $entity->getSummary(),
            position: $entity->getPosition(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            content: $entity->getContent() ?? '',
            videoProvider: $entity->getVideoProvider() !== null ? LearningVideoProvider::from($entity->getVideoProvider()) : null,
            videoUrl: $entity->getVideoUrl(),
            externalVideoId: $entity->getExternalVideoId(),
        );
    }

    public function toEntity(Lesson $model, ?LessonEntity $entity = null): LessonEntity
    {
        return ($entity ?? new LessonEntity())
            ->setModuleId($model->moduleId)
            ->setTitle($model->title)
            ->setSummary($model->summary)
            ->setContent($model->content !== '' ? $model->content : null)
            ->setVideoProvider($model->videoProvider?->value)
            ->setVideoUrl($model->videoUrl)
            ->setExternalVideoId($model->externalVideoId)
            ->setPosition($model->position);
    }
}
