<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Enum;

enum PaymentProvider: string
{
    case FAKE = 'FAKE';
    case KKIAPAY = 'KKIAPAY';

    public function label(): string
    {
        return match ($this) {
            self::FAKE => 'Fake',
            self::KKIAPAY => 'KkiaPay',
        };
    }
}
