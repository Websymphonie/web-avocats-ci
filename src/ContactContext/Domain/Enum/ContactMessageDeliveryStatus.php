<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Enum;

enum ContactMessageDeliveryStatus: string
{
    case PENDING = 'PENDING';
    case SENT = 'SENT';
    case FAILED = 'FAILED';
}
