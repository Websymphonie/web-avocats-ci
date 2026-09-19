<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Component\Notification;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('FilterNotificationComponent', template: 'notifications/components/filter_notification_component.html.twig')]
class FilterNotificationComponents
{
    public ?FormView $form = null;
    public ?string $id = null;
    public ?string $title = null;
}