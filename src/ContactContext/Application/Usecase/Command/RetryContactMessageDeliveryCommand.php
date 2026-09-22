<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\Command;

final readonly class RetryContactMessageDeliveryCommand
{
    public function __construct(public string $messageUuid)
    {
    }
}
