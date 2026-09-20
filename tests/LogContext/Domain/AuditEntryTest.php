<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Domain;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\LogContext\Domain\Model\Audit\AuditAction;
use Websymphonie\LogContext\Domain\Model\Audit\AuditActor;
use Websymphonie\LogContext\Domain\Model\Audit\AuditContext;
use Websymphonie\LogContext\Domain\Model\Audit\AuditEntry;

final class AuditEntryTest extends TestCase
{
    public function testItValidatesTheStructuredActionAndContext(): void
    {
        self::expectException(InvalidArgumentException::class);
        new AuditAction('Learning Training Published');
    }

    public function testItAllowsAUserOrSystemActorWithoutAUserRelation(): void
    {
        $entry = new AuditEntry(
            id: null,
            uuid: null,
            context: new AuditContext('LEARNING'),
            action: new AuditAction('learning.training.published'),
            actor: new AuditActor(AuditActorType::SYSTEM, 'console'),
            targetType: null,
            targetId: null,
            metadata: ['status' => 'PUBLISHED'],
            deduplicationKey: null,
            occurredAt: new DateTimeImmutable(),
            createdAt: null,
        );

        self::assertSame('SYSTEM', $entry->actor->type->value);
        self::assertSame('PUBLISHED', $entry->metadata['status']);
    }

    public function testItRejectsComplexMetadata(): void
    {
        self::expectException(InvalidArgumentException::class);
        new AuditEntry(
            id: null,
            uuid: null,
            context: new AuditContext('LEARNING'),
            action: new AuditAction('learning.training.published'),
            actor: new AuditActor(AuditActorType::USER, 'user-1'),
            targetType: null,
            targetId: null,
            metadata: ['object' => new \stdClass()],
            deduplicationKey: null,
            occurredAt: new DateTimeImmutable(),
            createdAt: null,
        );
    }
}
