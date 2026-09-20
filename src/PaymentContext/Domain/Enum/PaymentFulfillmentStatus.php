<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Enum;

enum PaymentFulfillmentStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'À traiter',
            self::COMPLETED => 'Accès activé',
        };
    }
}
