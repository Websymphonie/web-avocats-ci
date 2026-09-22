<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Entity\ContactMessage\ContactMessageEntity;

final class ContactMessageFactory
{
    public function fromEntity(ContactMessageEntity $entity): ContactMessage
    {
        return new ContactMessage(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            fullName: $entity->getFullName(),
            email: $entity->getEmail(),
            phone: $entity->getPhone(),
            subject: $entity->getSubject(),
            message: $entity->getMessage(),
            consentAt: $entity->getConsentAt(),
            submittedAt: $entity->getSubmittedAt(),
            deliveryStatus: $entity->getDeliveryStatus(),
            sentAt: $entity->getSentAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(ContactMessage $model, ?ContactMessageEntity $entity = null): ContactMessageEntity
    {
        $entity ??= new ContactMessageEntity();
        return $entity
            ->setFullName($model->fullName)
            ->setEmail($model->email)
            ->setPhone($model->phone)
            ->setSubject($model->subject)
            ->setMessage($model->message)
            ->setConsentAt($model->consentAt)
            ->setSubmittedAt($model->submittedAt)
            ->setDeliveryStatus($model->deliveryStatus)
            ->setSentAt($model->sentAt);
    }
}
