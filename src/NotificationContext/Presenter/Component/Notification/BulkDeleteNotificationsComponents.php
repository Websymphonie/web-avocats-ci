<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Component\Notification;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('BulkDeleteNotifications', template: 'notifications/components/bulk_delete_notifications_component.html.twig')]
final class BulkDeleteNotificationsComponents
{
    public string $formId;
}
