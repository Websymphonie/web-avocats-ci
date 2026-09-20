<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

final readonly class TrainingReference
{
    public function __construct(public int $id, public string $uuid, public string $title, public string $status, public string $accessType) {}

    public function isPublishedPaid(): bool
    {
        return $this->status === 'PUBLISHED' && $this->accessType === 'PAID';
    }
}
