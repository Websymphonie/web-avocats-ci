<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Twig;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationAccessEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationActionEnum;
use Websymphonie\NotificationContext\Domain\Enum\Notification\NotificationTypeEnum;
use Websymphonie\SharedContext\Domain\Enum\ColorEnum;

class NotificationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_type_badge', [$this, 'renderTypeBadge'], ['is_safe' => ['html']]),
            new TwigFunction('notification_action_badge', [$this, 'renderActionBadge'], ['is_safe' => ['html']]),
            new TwigFunction('notification_access_badge', [$this, 'renderAccessBadge'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws Exception
     */
    public function renderTypeBadge(NotificationTypeEnum $type): string
    {
        $label = NotificationTypeEnum::getText($type->value);
        $color = NotificationTypeEnum::getBadgeClass($type->value);

        return sprintf('<span class="badge bg-%s-subtle text-%s font-size-12">%s</span>', $color, $color, $label);
    }

    public function renderActionBadge(NotificationActionEnum $action): string
    {
        $label = NotificationActionEnum::getText($action->value);
        $color = NotificationActionEnum::getBadge($action->value);

        return sprintf('<span class="badge bg-%s-subtle text-%s font-size-12">%s</span>', $color, $color, $label);
    }

    /**
     * @throws Exception
     */
    public function renderAccessBadge(NotificationAccessEnum $access): string
    {
        $label = NotificationAccessEnum::getText($access->value);
        $color = NotificationAccessEnum::getBadgeClass($access->value);

        return sprintf('<span class="badge bg-%s-subtle text-%s font-size-12">%s</span>', $color, $color, $label);
    }
}
