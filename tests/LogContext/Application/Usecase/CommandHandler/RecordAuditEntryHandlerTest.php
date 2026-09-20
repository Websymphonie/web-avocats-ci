<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Application\Usecase\CommandHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\LogContext\Application\Service\SensitiveLogDataSanitizer;
use Websymphonie\LogContext\Application\Usecase\Command\Audit\RecordAuditEntryCommand;
use Websymphonie\LogContext\Application\Usecase\CommandHandler\Audit\RecordAuditEntryHandler;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository;

final class RecordAuditEntryHandlerTest extends TestCase
{
    public function testMetadataIsSanitizedBeforePersistence(): void
    {
        $repository = $this->createMock(AuditEntryRepository::class);
        $repository->expects(self::once())
            ->method('record')
            ->with(self::callback(function (AuditEntry $entry): bool {
                self::assertSame(SensitiveLogDataSanitizer::REDACTED, $entry->metadata['password']);
                self::assertSame('PUBLISHED', $entry->metadata['status']);

                return true;
            }))
            ->willReturnCallback(static fn (AuditEntry $entry): AuditEntry => $entry);

        $entry = (new RecordAuditEntryHandler($repository, new SensitiveLogDataSanitizer()))(
            new RecordAuditEntryCommand(
                context: 'LEARNING',
                action: 'learning.training.published',
                actorType: AuditActorType::USER,
                actorId: 'user-uuid',
                metadata: ['password' => 'secret', 'status' => 'PUBLISHED'],
            ),
        );

        self::assertSame('learning.training.published', $entry->action->value);
    }

    public function testDuplicateKeyReturnsTheAlreadyRecordedEntry(): void
    {
        $existing = new AuditEntry(
            id: 8,
            uuid: '0199a2b7-2f1a-7b1c-8d1a-6cf8f875b9b1',
            context: new \Websymphonie\LogContext\Domain\Model\Audit\AuditContext('LEARNING'),
            action: new \Websymphonie\LogContext\Domain\Model\Audit\AuditAction('learning.training.published'),
            actor: new \Websymphonie\LogContext\Domain\Model\Audit\AuditActor(AuditActorType::SYSTEM, 'system'),
            targetType: 'Training',
            targetId: 'training-1',
            metadata: [],
            deduplicationKey: str_repeat('a', 64),
            occurredAt: new \DateTimeImmutable(),
            createdAt: new \DateTimeImmutable(),
        );
        $repository = $this->createMock(AuditEntryRepository::class);
        $repository->expects(self::once())->method('findByDeduplicationKey')->willReturn($existing);
        $repository->expects(self::never())->method('record');

        $result = (new RecordAuditEntryHandler($repository, new SensitiveLogDataSanitizer()))(
            new RecordAuditEntryCommand(
                context: 'LEARNING',
                action: 'learning.training.published',
                actorType: AuditActorType::SYSTEM,
                actorId: 'system',
                deduplicationKey: str_repeat('a', 64),
            ),
        );

        self::assertSame(8, $result->id);
    }
}
