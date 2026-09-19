<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\EventSubscriber\Form;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class AntispamSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $antispamLogger, private RequestStack $requestStack)
    {
    }

    /**
     * @return array<string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'checkContact'
        ];
    }

    public function checkContact(FormEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $data = $event->getData();
        if (!array_key_exists('phone', $data) || !array_key_exists('faxNumber', $data)) {
            throw new HttpException(400, "Don't touch my form please");
        }
        [
            'phone' => $phone,
            'faxNumber' => $faxNumber
        ] = $data;

        if ($phone !== "" || $faxNumber !== "") {
            $message = "Une potentielle tentative de robot spammeur ayant l'adresse IP suivante '{$request->getClientIp()}' a eu lieu. Le champ phone contenait '$phone' 
            et le champ fax contenait '$faxNumber'";
            $this->antispamLogger->info($message);
            throw new HttpException(403, "Go away dirty bot!");
        }
    }
}
