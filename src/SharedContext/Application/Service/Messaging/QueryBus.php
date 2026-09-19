<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Messaging;

interface QueryBus
{
    public function handle(object $message): mixed;
}