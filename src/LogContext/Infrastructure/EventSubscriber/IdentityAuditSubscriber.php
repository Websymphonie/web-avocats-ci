<?php

declare(strict_types=1);

namespace Websymphonie\LogContext\Infrastructure\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\IdentityContext\Domain\Event\PasswordChangedEvent;
use Websymphonie\IdentityContext\Domain\Event\UserRoleAssignedEvent;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;

final readonly class IdentityAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(private BusinessAuditRecorder $recorder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRoleAssignedEvent::class => 'onRoleAssigned',
            PasswordChangedEvent::class => 'onPasswordChanged',
        ];
    }

    public function onRoleAssigned(UserRoleAssignedEvent $event): void
    {
        $actorType = $event->actorUserId !== null ? AuditActorType::USER : AuditActorType::SYSTEM;
        $actorId = $event->actorUserId !== null ? (string) $event->actorUserId : 'identity_system';
        $this->recorder->record(UserRoleAssignedEvent::class, 'IDENTITY', 'identity.user.role_assigned', $actorType, $actorId, 'User', (string) $event->userId, ['role' => $event->role], new \DateTimeImmutable(), hash('sha256', implode('|', ['identity.user.role_assigned', $event->userId, $event->role])), (string) $event->userId);
    }

    public function onPasswordChanged(PasswordChangedEvent $event): void
    {
        $this->recorder->record(PasswordChangedEvent::class, 'IDENTITY', 'identity.user.password_changed', AuditActorType::USER, (string) $event->actorUserId, 'User', (string) $event->userId, [], new \DateTimeImmutable(), hash('sha256', implode('|', ['identity.user.password_changed', $event->userId, $event->actorUserId])), (string) $event->userId);
    }
}
