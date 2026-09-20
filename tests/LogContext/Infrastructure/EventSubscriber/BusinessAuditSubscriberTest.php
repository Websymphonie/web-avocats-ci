<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Infrastructure\EventSubscriber;

use DateTimeImmutable;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Websymphonie\ContentContext\Domain\Event\ContentLifecycleEvent;
use Websymphonie\IdentityContext\Domain\Event\PasswordChangedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleAssignedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleRemovedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentActivatedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentRevokedEvent;
use Websymphonie\LogContext\Application\Usecase\Command\Audit\RecordAuditEntryCommand;
use Websymphonie\LogContext\Infrastructure\EventSubscriber\BusinessAuditRecorder;
use Websymphonie\LogContext\Infrastructure\EventSubscriber\ContentAuditSubscriber;
use Websymphonie\LogContext\Infrastructure\EventSubscriber\IdentityAuditSubscriber;
use Websymphonie\LogContext\Infrastructure\EventSubscriber\LearningAuditSubscriber;
use Websymphonie\LogContext\Infrastructure\EventSubscriber\PaymentAuditSubscriber;
use Websymphonie\PaymentContext\Domain\Event\PaymentConfirmedEvent;
use Websymphonie\PaymentContext\Domain\Event\PaymentFailedEvent;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

final class BusinessAuditSubscriberTest extends TestCase
{
    public function testContentMappingKeepsActorTargetSnapshotAndDeduplication(): void
    {
        $commands = [];
        $subscriber = new ContentAuditSubscriber($this->recorderFor($commands));
        $occurredAt = new DateTimeImmutable('2026-09-20T10:00:00+00:00');

        $subscriber->onContentLifecycle(new ContentLifecycleEvent('NEWS', 'PUBLISHED', 'news-1', 'Titre', 'titre', 42, $occurredAt));

        /** @var RecordAuditEntryCommand $command */
        $command = $commands[0];
        self::assertSame('CONTENT', $command->context);
        self::assertSame('content.news.published', $command->action);
        self::assertSame('USER', $command->actorType->value);
        self::assertSame('42', $command->actorId);
        self::assertSame('News', $command->targetType);
        self::assertSame('news-1', $command->targetId);
        self::assertSame('Titre', $command->metadata['title']);
        self::assertSame(hash('sha256', 'content.news.published|news-1|2026-09-20T10:00:00.000000+00:00'), $command->deduplicationKey);
    }

    public function testStaticPageLifecycleUsesPageAuditTarget(): void
    {
        $commands = [];
        $subscriber = new ContentAuditSubscriber($this->recorderFor($commands));
        $subscriber->onContentLifecycle(new ContentLifecycleEvent('PAGE', 'UNPUBLISHED', 'page-1', 'À propos', 'a-propos', null));

        /** @var RecordAuditEntryCommand $command */
        $command = $commands[0];
        self::assertSame('content.page.unpublished', $command->action);
        self::assertSame('Page', $command->targetType);
        self::assertSame('content_system', $command->actorId);
        self::assertSame('page-1', $command->targetId);
    }

    public function testEnrollmentActorsDistinguishSelfAdminAndPayment(): void
    {
        $commands = [];
        $recorder = $this->recorderFor($commands);
        $subscriber = new LearningAuditSubscriber($recorder);

        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('self', 12, 10, 'SELF_SERVICE', 'Libre', '2026-09-20T10:00:00.000000+00:00'));
        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('admin', 12, 10, 'ADMIN_GRANT', 'Admin', 'activation-2', 99));
        $subscriber->onEnrollmentActivated(new EnrollmentActivatedEvent('paid', 12, 10, 'PAYMENT', 'Payée', 'activation-3'));
        $subscriber->onEnrollmentRevoked(new EnrollmentRevokedEvent('admin', 12, 10, 'Admin', 'revocation-1', 99));

        self::assertCount(4, $commands);
        self::assertSame('learning.enrollment.self_enrolled', $commands[0]->action);
        self::assertSame('USER', $commands[0]->actorType->value);
        self::assertSame('12', $commands[0]->actorId);
        self::assertSame('learning.enrollment.granted', $commands[1]->action);
        self::assertSame('99', $commands[1]->actorId);
        self::assertSame('learning.enrollment.payment_activated', $commands[2]->action);
        self::assertSame('SYSTEM', $commands[2]->actorType->value);
        self::assertSame('kkiapay_webhook', $commands[2]->actorId);
        self::assertSame('learning.enrollment.revoked', $commands[3]->action);
        self::assertSame('99', $commands[3]->actorId);
    }

    public function testPaymentReplayUsesTheSameDeduplicationKeyAndNeverContainsSecrets(): void
    {
        $commands = [];
        $subscriber = new PaymentAuditSubscriber($this->recorderFor($commands));
        $occurredAt = new DateTimeImmutable('2026-09-20T10:00:00+00:00');
        $event = new PaymentConfirmedEvent('payment-1', 12, 10, 5000, 'XOF', 'KKIAPAY', 'transaction-1', $occurredAt);

        $subscriber->onPaymentConfirmed($event);
        $subscriber->onPaymentConfirmed($event);

        self::assertCount(2, $commands);
        self::assertSame('payment.payment.confirmed', $commands[0]->action);
        self::assertSame($commands[0]->deduplicationKey, $commands[1]->deduplicationKey);
        self::assertSame('kkiapay_webhook', $commands[0]->actorId);
        self::assertArrayNotHasKey('secret', $commands[0]->metadata);
        self::assertSame('transaction-1', $commands[0]->metadata['providerReference']);
    }

    public function testIdentityMappingAndAuditFailureAreObservable(): void
    {
        $commands = [];
        $subscriber = new IdentityAuditSubscriber($this->recorderFor($commands));
        $subscriber->onRoleAssigned(new UserRoleAssignedEvent(12, 'ROLE_AVOCAT', 99));
        $subscriber->onRoleRemoved(new UserRoleRemovedEvent(12, 'ROLE_USER', 99));
        $subscriber->onPasswordChanged(new PasswordChangedEvent(12, 12));

        self::assertSame('identity.user.role_assigned', $commands[0]->action);
        self::assertSame('99', $commands[0]->actorId);
        self::assertSame('identity.user.role_removed', $commands[1]->action);
        self::assertSame('ROLE_USER', $commands[1]->metadata['role']);
        self::assertSame('identity.user.password_changed', $commands[2]->action);
        self::assertSame([], $commands[2]->metadata);

        $handler = new TestHandler();
        $logger = new Logger('audit-test', [$handler]);
        $bus = new class implements CommandBus {
            public function handle(object $message): mixed
            {
                throw new \RuntimeException('audit database unavailable');
            }
        };
        $recorder = new BusinessAuditRecorder($bus, $logger);
        $recorder->record('TestEvent', 'LEARNING', 'learning.training.published', \Websymphonie\LogContext\Domain\Enum\AuditActorType::SYSTEM, 'system', 'Training', 'training-1', [], new DateTimeImmutable(), str_repeat('a', 64), 'training-1');

        self::assertTrue($handler->hasErrorRecords());
        self::assertStringContainsString('TestEvent', (string) $handler->getRecords()[0]['context']['eventType']);
        self::assertSame(\RuntimeException::class, $handler->getRecords()[0]['context']['failureClass']);
    }

    /** @param list<object> $commands */
    private function recorderFor(array &$commands): BusinessAuditRecorder
    {
        $bus = new class($commands) implements CommandBus {
            /** @var list<object> */
            private array $commands;

            /** @param list<object> $commands */
            public function __construct(array &$commands)
            {
                $this->commands =& $commands;
            }

            public function handle(object $message): mixed
            {
                $this->commands[] = $message;
                return null;
            }
        };

        return new BusinessAuditRecorder($bus, new Logger('audit-test', [new TestHandler()]));
    }
}
