<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        if (in_array('application/json', $event->getRequest()->getAcceptableContentTypes(), true)) {
            $event->setResponse(new JsonResponse(['message' => 'Vous êtes déconnecté', 'status' => true], Response::HTTP_OK));
        }
    }
}
