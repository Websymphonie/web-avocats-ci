<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Presenter\Component\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;

#[AsTwigComponent('NotificationCount', template: 'notifications/components/notifications_count_component.html.twig')]
class NotificationCountComponents extends AbstractController
{
    public int $count = 0;

    public function __construct(
        private readonly NotificationModelRepository $repository,
        private readonly CurrentUserProvider         $currentUserProvider,
    )
    {
    }

    public function mount(): void
    {
        $user = $this->currentUserProvider->getUser();
        $this->count = $this->repository->countNotifs($user);
    }
}