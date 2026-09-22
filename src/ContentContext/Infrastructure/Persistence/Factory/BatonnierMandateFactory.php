<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;

final class BatonnierMandateFactory
{
    public function fromEntity(BatonnierMandateEntity $entity): BatonnierMandate
    {
        return new BatonnierMandate(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            fullName: $entity->getFullName(),
            portraitMediaId: $entity->getPortraitMediaId(),
            mandateStartedAt: $entity->getMandateStartedAt(),
            mandateEndedAt: $entity->getMandateEndedAt(),
            summary: $entity->getSummary(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(BatonnierMandate $mandate, ?BatonnierMandateEntity $entity = null): BatonnierMandateEntity
    {
        $entity ??= new BatonnierMandateEntity();

        return $entity
            ->setFullName($mandate->fullName)
            ->setPortraitMediaId($mandate->portraitMediaId)
            ->setMandateStartedAt($mandate->mandateStartedAt)
            ->setMandateEndedAt($mandate->mandateEndedAt)
            ->setSummary($mandate->summary);
    }
}
