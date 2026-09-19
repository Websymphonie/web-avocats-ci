<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Messaging;

interface CommandBus
{
    public function handle(object $message): mixed;
}