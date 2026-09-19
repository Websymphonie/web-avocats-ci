<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Messenger;

use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\MessageBus;

final readonly class MessengerMessageBus implements MessageBus
{

    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatch(object $message): void
    {
        $this->messageBus->dispatch($message);
    }
}