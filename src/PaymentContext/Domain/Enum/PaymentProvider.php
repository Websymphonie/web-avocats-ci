<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Enum;

enum PaymentProvider: string
{
    case FAKE = 'FAKE';

    public function label(): string
    {
        return 'Fake';
    }
}
