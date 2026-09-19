<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Infrastructure\Framework\Symfony\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class SymfonyEventDispatcher implements EventDispatcher
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @param array<int, object> $events
     * @return void
     */
    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}