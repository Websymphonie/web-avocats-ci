<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\EventDispatcher;

trait EventEmitterFeature
{
    /** @var list<object> */
    private array $emittedEvents = [];

    public function emitEvent(object $event): void
    {
        $this->emittedEvents[] = $event;
    }

    /** @return list<object> */
    public function releaseEvents(): array
    {
        $events = $this->emittedEvents;
        $this->emittedEvents = [];

        return $events;
    }
}
