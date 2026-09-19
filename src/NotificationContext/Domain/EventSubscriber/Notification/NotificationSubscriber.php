<?php declare(strict_types=1);

namespace Websymphonie\NotificationContext\Domain\EventSubscriber\Notification;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Websymphonie\NotificationContext\Application\Usecase\Notification\SendNotificationUseCase;
use Websymphonie\NotificationContext\Domain\Service\Event\NotificationEvent;

final readonly class NotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SendNotificationUseCase $sendNotificationUseCase
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotificationEvent::class => 'onSendNotification',
        ];
    }

    public function onSendNotification(NotificationEvent $event): void
    {
        $this->sendNotificationUseCase->execute(
            title: $event->title,
            message: $event->message,
            type: $event->type,
            action: $event->action,
            access: $event->access,
            roles: $event->roles,
            userIds: $event->userIds,
            context: $event->context
        );
    }
}