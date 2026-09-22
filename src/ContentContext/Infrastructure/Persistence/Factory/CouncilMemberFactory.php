<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Infrastructure\Persistence\Factory;

use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;

final class CouncilMemberFactory
{
    public function fromEntity(CouncilMemberEntity $entity): CouncilMember
    {
        return new CouncilMember(
            id: $entity->getId() ?? 0,
            uuid: $entity->getUuidAsString() ?? '',
            fullName: $entity->getFullName(),
            function: $entity->getFunction(),
            portraitMediaId: $entity->getPortraitMediaId(),
            sortOrder: $entity->getSortOrder(),
            mandateStartedAt: $entity->getMandateStartedAt(),
            mandateEndedAt: $entity->getMandateEndedAt(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
        );
    }

    public function toEntity(CouncilMember $member, ?CouncilMemberEntity $entity = null): CouncilMemberEntity
    {
        $entity ??= new CouncilMemberEntity();

        return $entity
            ->setFullName($member->fullName)
            ->setFunction($member->function)
            ->setPortraitMediaId($member->portraitMediaId)
            ->setSortOrder($member->sortOrder)
            ->setMandateStartedAt($member->mandateStartedAt)
            ->setMandateEndedAt($member->mandateEndedAt);
    }
}
