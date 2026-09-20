<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Application\Usecase\CommandHandler\Audit;

use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;
use Websymphonie\LogContext\Application\Usecase\Command\Audit\RecordAuditEntryCommand;
use Websymphonie\LogContext\Domain\Model\Audit\AuditAction;
use Websymphonie\LogContext\Domain\Model\Audit\AuditActor;
use Websymphonie\LogContext\Domain\Model\Audit\AuditContext;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class RecordAuditEntryHandler implements CommandHandler
{
    public function __construct(
        private AuditEntryRepository $repository,
        private SensitiveLogDataSanitizer $sanitizer,
    ) {
    }

    public function __invoke(RecordAuditEntryCommand $command): AuditEntry
    {
        if ($command->deduplicationKey !== null) {
            $existing = $this->repository->findByDeduplicationKey($command->deduplicationKey);
            if ($existing !== null) {
                return $existing;
            }
        }

        $entry = new AuditEntry(
            id: null,
            uuid: null,
            context: new AuditContext($command->context),
            action: new AuditAction($command->action),
            actor: new AuditActor($command->actorType, $command->actorId),
            targetType: $command->targetType,
            targetId: $command->targetId,
            metadata: $this->sanitizer->sanitizeMetadata($command->metadata),
            deduplicationKey: $command->deduplicationKey,
            occurredAt: $command->occurredAt ?? new DateTimeImmutable(),
            createdAt: null,
        );

        try {
            return $this->repository->record($entry);
        } catch (UniqueConstraintViolationException $exception) {
            if ($command->deduplicationKey === null) {
                throw $exception;
            }

            $existing = $this->repository->findByDeduplicationKey($command->deduplicationKey);
            if ($existing !== null) {
                return $existing;
            }

            throw $exception;
        }
    }
}
