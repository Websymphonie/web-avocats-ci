<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuditEntry;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Repository\AuditEntry\AuditEntryRepository;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\IdTrait;
use Websymphonie\SharedContext\Infrastructure\Persistence\Doctrine\Feature\UuidTrait;

#[ORM\Entity(repositoryClass: AuditEntryRepository::class)]
#[ORM\Table(name: 'audit_entry')]
#[ORM\Index(name: 'idx_audit_entry_occurred_at', columns: ['occurred_at'])]
#[ORM\Index(name: 'idx_audit_entry_context', columns: ['context'])]
#[ORM\Index(name: 'idx_audit_entry_action', columns: ['action'])]
#[ORM\Index(name: 'idx_audit_entry_actor_id', columns: ['actor_id'])]
#[ORM\Index(name: 'idx_audit_entry_target', columns: ['target_type', 'target_id'])]
#[ORM\HasLifecycleCallbacks]
final class AuditEntry
{
    use IdTrait;
    use UuidTrait;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $context;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $action;

    #[ORM\Column(name: 'actor_type', type: Types::STRING, length: 20)]
    private string $actorType;

    #[ORM\Column(name: 'actor_id', type: Types::STRING, length: 191, nullable: true)]
    private ?string $actorId;

    #[ORM\Column(name: 'target_type', type: Types::STRING, length: 100, nullable: true)]
    private ?string $targetType;

    #[ORM\Column(name: 'target_id', type: Types::STRING, length: 191, nullable: true)]
    private ?string $targetId;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $metadata;

    #[ORM\Column(name: 'deduplication_key', type: Types::STRING, length: 64, nullable: true, unique: true)]
    private ?string $deduplicationKey;

    #[ORM\Column(name: 'occurred_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $occurredAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        string $context,
        string $action,
        string $actorType,
        ?string $actorId,
        ?string $targetType,
        ?string $targetId,
        array $metadata,
        ?string $deduplicationKey,
        DateTimeImmutable $occurredAt,
        ?Uuid $uuid = null,
    ) {
        $this->uuid = $uuid ?? Uuid::v7();
        $this->context = $context;
        $this->action = $action;
        $this->actorType = $actorType;
        $this->actorId = $actorId;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->metadata = $metadata;
        $this->deduplicationKey = $deduplicationKey;
        $this->occurredAt = $occurredAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt ??= new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getContext(): string { return $this->context; }
    public function getAction(): string { return $this->action; }
    public function getActorType(): string { return $this->actorType; }
    public function getActorId(): ?string { return $this->actorId; }
    public function getTargetType(): ?string { return $this->targetType; }
    public function getTargetId(): ?string { return $this->targetId; }

    /** @return array<string, mixed> */
    public function getMetadata(): array { return $this->metadata; }

    public function getDeduplicationKey(): ?string { return $this->deduplicationKey; }
    public function getOccurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCreatedAt(): ?DateTimeImmutable { return $this->createdAt; }
}
