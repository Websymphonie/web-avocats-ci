<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Component\Notification;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;

#[AsTwigComponent('NotificationsComponent', template: 'notifications/components/notifications_component.html.twig')]
class NotificationsComponents
{
    public ?User $user = null;
    /** @var list<Notifications>|null */
    public ?array $notifications = [];
    public ?int $countNotification = 0;

    public function __construct(private readonly NotificationModelRepository $repository)
    {
    }

    public function mount(): void
    {
        $this->notifications = $this->repository->getUnreadNotifs($this->user);
        $this->countNotification = $this->repository->countNotifs($this->user);
    }
}
