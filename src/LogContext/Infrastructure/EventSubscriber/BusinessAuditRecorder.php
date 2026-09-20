<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Websymphonie\LogContext\Application\Usecase\Command\Audit\RecordAuditEntryCommand;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

final readonly class BusinessAuditRecorder
{
    public function __construct(
        private CommandBus $commandBus,
        private LoggerInterface $logger,
    ) {
    }

    /** @param array<string, mixed> $metadata */
    public function record(
        string $eventType,
        string $context,
        string $action,
        AuditActorType $actorType,
        ?string $actorId,
        ?string $targetType,
        ?string $targetId,
        array $metadata,
        DateTimeImmutable $occurredAt,
        string $deduplicationKey,
        string $businessReference,
    ): void {
        try {
            $this->commandBus->handle(new RecordAuditEntryCommand(
                context: $context,
                action: $action,
                actorType: $actorType,
                actorId: $actorId,
                targetType: $targetType,
                targetId: $targetId,
                metadata: $metadata,
                occurredAt: $occurredAt,
                deduplicationKey: $deduplicationKey,
            ));
        } catch (\Throwable $exception) {
            $this->logger->error('Le business audit n\'a pas pu être persisté.', [
                'eventType' => $eventType,
                'auditAction' => $action,
                'businessReference' => $businessReference,
                'actorType' => $actorType->value,
                'actorId' => $actorId,
                'failureClass' => $exception::class,
            ]);
        }
    }
}
