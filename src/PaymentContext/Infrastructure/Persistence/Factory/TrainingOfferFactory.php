<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Factory;

use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;

final class TrainingOfferFactory
{
    public function fromEntity(TrainingOfferEntity $entity): TrainingOffer
    {
        return new TrainingOffer($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getTrainingId(), $entity->getAmount(), $entity->getCurrency(), $entity->isActive(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(TrainingOffer $model, ?TrainingOfferEntity $entity = null): TrainingOfferEntity
    {
        return ($entity ?? new TrainingOfferEntity())->setTrainingId($model->trainingId)->setAmount($model->amount)->setCurrency($model->currency)->setActive($model->active);
    }
}
