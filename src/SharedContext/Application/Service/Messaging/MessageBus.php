<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Application\Service\Messaging;

interface MessageBus
{
    public function dispatch(AsyncMessage $message): void;
}