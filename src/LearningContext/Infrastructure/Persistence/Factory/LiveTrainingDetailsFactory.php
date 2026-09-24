<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Infrastructure\Persistence\Factory;

use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Model\ExternalVideoSource;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;

final class LiveTrainingDetailsFactory
{
    public function fromEntity(LiveTrainingDetailsEntity $entity): LiveTrainingDetails
    {
        return new LiveTrainingDetails(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            trainingId: $entity->getTrainingId(),
            startsAt: $entity->getStartsAt(),
            endsAt: $entity->getEndsAt(),
            deliveryMode: $entity->getDeliveryMode(),
            location: $entity->getLocation(),
            joinUrl: $entity->getJoinUrl(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            liveSource: $entity->getStreamProvider() !== null && $entity->getExternalStreamId() !== null
                ? new ExternalVideoSource($entity->getStreamProvider(), $entity->getExternalStreamId())
                : null,
            replaySource: $entity->getReplayProvider() !== null && $entity->getReplayExternalId() !== null
                ? new ExternalVideoSource($entity->getReplayProvider(), $entity->getReplayExternalId())
                : null,
        );
    }

    public function toEntity(LiveTrainingDetails $model, ?LiveTrainingDetailsEntity $entity = null): LiveTrainingDetailsEntity
    {
        return ($entity ?? new LiveTrainingDetailsEntity())
            ->setTrainingId($model->trainingId)
            ->setStartsAt($model->startsAt)
            ->setEndsAt($model->endsAt)
            ->setDeliveryMode($model->deliveryMode)
            ->setLocation($model->location)
            ->setJoinUrl($model->joinUrl)
            ->setStreamProvider($model->liveSource?->provider)
            ->setExternalStreamId($model->liveSource?->externalId)
            ->setReplayProvider($model->replaySource?->provider)
            ->setReplayExternalId($model->replaySource?->externalId);
    }
}
