<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Factory;

use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\Payment\PaymentEntity;

final class PaymentFactory
{
    public function fromEntity(PaymentEntity $entity): Payment
    {
        return new Payment($entity->getId() ?? 0, $entity->getUuidAsString() ?? '', $entity->getUserId(), $entity->getTrainingId(), $entity->getTrainingOfferId(), $entity->getAmount(), $entity->getCurrency(), $entity->getStatus(), $entity->getProvider(), $entity->getProviderReference(), $entity->getIdempotencyKey(), $entity->getConfirmedAt(), $entity->getFailedAt(), $entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function toEntity(Payment $model, ?PaymentEntity $entity = null): PaymentEntity
    {
        $entity = $entity ?? new PaymentEntity();
        if ($model->uuid !== '') {
            $entity->setUuidFromString($model->uuid);
        }

        return $entity->setUserId($model->userId)->setTrainingId($model->trainingId)->setTrainingOfferId($model->trainingOfferId)->setAmount($model->amount)->setCurrency($model->currency)->setStatus($model->status)->setProvider($model->provider)->setProviderReference($model->providerReference)->setIdempotencyKey($model->idempotencyKey)->setConfirmedAt($model->confirmedAt)->setFailedAt($model->failedAt);
    }
}
