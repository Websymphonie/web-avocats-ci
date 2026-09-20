<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Factory\AuditEntry;

use Symfony\Component\Uid\Uuid;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\LogContext\Domain\Model\Audit\AuditAction;
use Websymphonie\LogContext\Domain\Model\Audit\AuditActor;
use Websymphonie\LogContext\Domain\Model\Audit\AuditContext;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuditEntry\AuditEntry as AuditEntryEntity;

final readonly class AuditEntryFactory
{
    public function toEntity(AuditEntry $entry): AuditEntryEntity
    {
        return new AuditEntryEntity(
            context: $entry->context->value,
            action: $entry->action->value,
            actorType: $entry->actor->type->value,
            actorId: $entry->actor->id,
            targetType: $entry->targetType,
            targetId: $entry->targetId,
            metadata: $entry->metadata,
            deduplicationKey: $entry->deduplicationKey,
            occurredAt: $entry->occurredAt,
            uuid: $entry->uuid !== null ? Uuid::fromString($entry->uuid) : null,
        );
    }

    public function fromEntity(?AuditEntryEntity $entity): ?AuditEntry
    {
        if ($entity === null) {
            return null;
        }

        return new AuditEntry(
            id: $entity->getId(),
            uuid: $entity->getUuidAsString(),
            context: new AuditContext($entity->getContext()),
            action: new AuditAction($entity->getAction()),
            actor: new AuditActor(AuditActorType::from($entity->getActorType()), $entity->getActorId()),
            targetType: $entity->getTargetType(),
            targetId: $entity->getTargetId(),
            metadata: $entity->getMetadata(),
            deduplicationKey: $entity->getDeduplicationKey(),
            occurredAt: $entity->getOccurredAt(),
            createdAt: $entity->getCreatedAt(),
        );
    }

    /**
     * @param list<AuditEntryEntity> $entities
     * @return list<AuditEntry>
     */
    public function fromEntities(array $entities): array
    {
        return array_values(array_filter(array_map($this->fromEntity(...), $entities)));
    }
}
