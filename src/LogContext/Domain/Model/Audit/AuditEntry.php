<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Domain\Model\Audit;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AuditEntry
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public AuditContext $context,
        public AuditAction $action,
        public AuditActor $actor,
        public ?string $targetType,
        public ?string $targetId,
        public array $metadata,
        public ?string $deduplicationKey,
        public DateTimeImmutable $occurredAt,
        public ?DateTimeImmutable $createdAt,
    ) {
        if (($targetType === null) !== ($targetId === null)) {
            throw new InvalidArgumentException('La cible d’audit doit fournir son type et son identifiant ensemble.');
        }

        if ($targetType !== null && (trim($targetType) === '' || strlen($targetType) > 100)) {
            throw new InvalidArgumentException('Le type de cible d’audit est invalide.');
        }

        if ($targetId !== null && (trim($targetId) === '' || strlen($targetId) > 191)) {
            throw new InvalidArgumentException('L’identifiant de cible d’audit est invalide.');
        }

        if ($deduplicationKey !== null && (trim($deduplicationKey) === '' || strlen($deduplicationKey) > 64)) {
            throw new InvalidArgumentException('La clé de déduplication d’audit est invalide.');
        }

        $this->assertSimpleMetadata($metadata);
    }

    private function assertSimpleMetadata(mixed $value): void
    {
        if ($value === null || is_scalar($value)) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->assertSimpleMetadata($item);
            }

            return;
        }

        throw new InvalidArgumentException('Les métadonnées d’audit doivent être composées de valeurs simples.');
    }
}
