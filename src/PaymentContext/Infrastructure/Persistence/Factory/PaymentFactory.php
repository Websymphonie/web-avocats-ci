<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Persistence\Factory;

use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\Payment\PaymentEntity;

final class PaymentFactory
{
    public function fromEntity(PaymentEntity $entity): Payment
    {
        return new Payment(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            userId: $entity->getUserId(),
            trainingId: $entity->getTrainingId(),
            trainingOfferId: $entity->getTrainingOfferId(),
            amount: $entity->getAmount(),
            currency: $entity->getCurrency(),
            status: $entity->getStatus(),
            provider: $entity->getProvider(),
            providerReference: $entity->getProviderReference(),
            idempotencyKey: $entity->getIdempotencyKey(),
            confirmedAt: $entity->getConfirmedAt(),
            failedAt: $entity->getFailedAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            fulfillmentStatus: $entity->getFulfillmentStatus(),
            fulfillmentCompletedAt: $entity->getFulfillmentCompletedAt(),
            fulfillmentAttempts: $entity->getFulfillmentAttempts(),
            lastFulfillmentAttemptAt: $entity->getLastFulfillmentAttemptAt(),
        );
    }

    public function toEntity(Payment $model, ?PaymentEntity $entity = null): PaymentEntity
    {
        $entity = $entity ?? new PaymentEntity();
        if ($model->uuid !== '') {
            $entity->setUuidFromString($model->uuid);
        }

        return $entity->setUserId($model->userId)->setTrainingId($model->trainingId)->setTrainingOfferId($model->trainingOfferId)->setAmount($model->amount)->setCurrency($model->currency)->setStatus($model->status)->setFulfillmentStatus($model->fulfillmentStatus)->setProvider($model->provider)->setProviderReference($model->providerReference)->setIdempotencyKey($model->idempotencyKey)->setConfirmedAt($model->confirmedAt)->setFailedAt($model->failedAt)->setFulfillmentCompletedAt($model->fulfillmentCompletedAt)->setFulfillmentAttempts($model->fulfillmentAttempts)->setLastFulfillmentAttemptAt($model->lastFulfillmentAttemptAt);
    }
}
