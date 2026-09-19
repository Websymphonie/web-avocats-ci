<?php
declare(strict_types=1);

namespace Websymphonie\AuthContext\Presenter\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (in_array('application/json', $event->getRequest()->getAcceptableContentTypes(), true)) {
            $data = [
                "status" => 'error',
                "message" => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($data));
        }
    }
}
