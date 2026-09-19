<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Messenger;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

final  class MessengerCommandBus implements CommandBus
{
    use HandleTrait {
        HandleTrait::handle as messageHandle;
    }

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->messageBus = $commandBus;
    }

    /**
     * @throws Throwable
     */
    public function handle(object $message): mixed
    {
        try {
            return $this->messageHandle($message);
        } catch (HandlerFailedException $e) {
            while ($e instanceof HandlerFailedException) {
                /** @var Throwable $e */
                $e = $e->getPrevious();
            }

            throw $e;
        }
    }
}