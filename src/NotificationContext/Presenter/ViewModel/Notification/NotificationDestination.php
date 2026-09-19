<?php

declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\ViewModel\Notification;

final readonly class NotificationDestination
{
    public function __construct(
        public string $label,
        public string $url,
        public string $icon,
    ) {
    }
}
