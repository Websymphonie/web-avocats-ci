<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Domain\Enum;

enum ContactMessageDeliveryStatus: string
{
    case PENDING = 'PENDING';
    case SENT = 'SENT';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::SENT => 'Envoyé',
            self::FAILED => 'Échec d’envoi',
        };
    }
}
