<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\PaymentContext\Domain\Exception\PaymentException;

final class TrainingOffer
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $trainingId,
        public int $amount,
        public string $currency = 'XOF',
        public bool $active = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
        self::assertMoney($amount, $currency);
    }

    public function update(int $amount, string $currency, bool $active): void
    {
        self::assertMoney($amount, $currency);
        $this->amount = $amount;
        $this->currency = strtoupper(trim($currency));
        $this->active = $active;
    }

    private static function assertMoney(int $amount, string $currency): void
    {
        if ($amount <= 0 || preg_match('/^[A-Z]{3}$/', strtoupper(trim($currency))) !== 1) {
            throw new PaymentException('Le montant et la devise du tarif sont invalides.');
        }
    }
}
