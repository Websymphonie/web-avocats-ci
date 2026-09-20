<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

enum PaymentFulfillmentResult: string
{
    case COMPLETED = 'COMPLETED';
    case ALREADY_COMPLETED = 'ALREADY_COMPLETED';
}
