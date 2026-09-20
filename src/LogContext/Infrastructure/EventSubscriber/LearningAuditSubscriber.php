<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\LearningContext\Domain\Event\EnrollmentActivatedEvent;
use Websymphonie\LearningContext\Domain\Event\EnrollmentRevokedEvent;
use Websymphonie\LearningContext\Domain\Event\TrainingLifecycleEvent;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final readonly class LearningAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(private BusinessAuditRecorder $recorder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TrainingLifecycleEvent::class => 'onTrainingLifecycle',
            EnrollmentActivatedEvent::class => 'onEnrollmentActivated',
            EnrollmentRevokedEvent::class => 'onEnrollmentRevoked',
        ];
    }

    public function onTrainingLifecycle(TrainingLifecycleEvent $event): void
    {
        $action = sprintf('learning.training.%s', strtolower($event->transition));
        $actorType = $event->actorUserId !== null ? AuditActorType::USER : AuditActorType::SYSTEM;
        $actorId = $event->actorUserId !== null ? (string) $event->actorUserId : 'learning_system';
        $this->recorder->record(
            eventType: TrainingLifecycleEvent::class,
            context: 'LEARNING',
            action: $action,
            actorType: $actorType,
            actorId: $actorId,
            targetType: 'Training',
            targetId: $event->trainingUuid,
            metadata: ['type' => $event->trainingType, 'previous_status' => $event->previousStatus, 'new_status' => $event->newStatus],
            occurredAt: $event->occurredAt,
            deduplicationKey: hash('sha256', implode('|', [$action, $event->trainingUuid, $event->occurredAt->format('Y-m-d\\TH:i:s.uP')])),
            businessReference: $event->trainingUuid,
        );
    }

    public function onEnrollmentActivated(EnrollmentActivatedEvent $event): void
    {
        [$action, $actorType, $actorId] = match ($event->source) {
            'SELF_SERVICE' => ['learning.enrollment.self_enrolled', AuditActorType::USER, (string) $event->userId],
            'ADMIN_GRANT' => ['learning.enrollment.granted', AuditActorType::USER, $event->actorUserId !== null ? (string) $event->actorUserId : 'learning_admin_grant'],
            'PAYMENT' => ['learning.enrollment.payment_activated', AuditActorType::SYSTEM, 'kkiapay_webhook'],
            default => ['learning.enrollment.activated', AuditActorType::SYSTEM, 'learning_system'],
        };
        $this->recorder->record(
            eventType: EnrollmentActivatedEvent::class,
            context: 'LEARNING',
            action: $action,
            actorType: $actorType,
            actorId: $actorId,
            targetType: 'Enrollment',
            targetId: $event->enrollmentUuid,
            metadata: ['learner_user_id' => $event->userId, 'training_id' => $event->trainingId, 'source' => $event->source, 'training_title' => $event->trainingTitle],
            occurredAt: $this->referenceDate($event->activationReference),
            deduplicationKey: hash('sha256', implode('|', [$action, $event->enrollmentUuid, $event->activationReference])),
            businessReference: $event->enrollmentUuid,
        );
    }

    public function onEnrollmentRevoked(EnrollmentRevokedEvent $event): void
    {
        $this->recorder->record(
            eventType: EnrollmentRevokedEvent::class,
            context: 'LEARNING',
            action: 'learning.enrollment.revoked',
            actorType: $event->actorUserId !== null ? AuditActorType::USER : AuditActorType::SYSTEM,
            actorId: $event->actorUserId !== null ? (string) $event->actorUserId : 'learning_admin',
            targetType: 'Enrollment',
            targetId: $event->enrollmentUuid,
            metadata: ['learner_user_id' => $event->userId, 'training_id' => $event->trainingId, 'training_title' => $event->trainingTitle],
            occurredAt: $this->referenceDate($event->revocationReference),
            deduplicationKey: hash('sha256', implode('|', ['learning.enrollment.revoked', $event->enrollmentUuid, $event->revocationReference])),
            businessReference: $event->enrollmentUuid,
        );
    }

    private function referenceDate(string $reference): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($reference);
        } catch (\Exception) {
            return new \DateTimeImmutable();
        }
    }
}
