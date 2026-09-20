<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Enum;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmé',
            self::FAILED => 'Échoué',
        };
    }
}
