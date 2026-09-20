<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Command;

final class SaveTrainingOfferCommand
{
    public function __construct(public int $trainingId = 0, public int $amount = 0, public string $currency = 'XOF', public bool $active = true) {}
}
