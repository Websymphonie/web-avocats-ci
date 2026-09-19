<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Domain\Service\EventDispatcher;

interface EventDispatcher
{
    /**
     * @param array<int, object> $events
     * @return void
     */
    public function dispatch(array $events): void;
}