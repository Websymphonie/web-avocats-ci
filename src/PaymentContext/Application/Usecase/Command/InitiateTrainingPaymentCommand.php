<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Command;

final readonly class InitiateTrainingPaymentCommand
{
    public function __construct(public int $userId, public int $trainingId, public string $idempotencyKey) {}
}
