<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Model;

use DateTimeImmutable;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentFulfillmentStatus;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\InvalidPaymentTransitionException;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventEmitterFeature;

final class Payment
{
    use EventEmitterFeature;

    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly int $userId,
        public readonly int $trainingId,
        public readonly ?int $trainingOfferId,
        public readonly int $amount,
        public readonly string $currency,
        public PaymentStatus $status = PaymentStatus::PENDING,
        public readonly PaymentProvider $provider = PaymentProvider::FAKE,
        public ?string $providerReference = null,
        public readonly string $idempotencyKey = '',
        public ?DateTimeImmutable $confirmedAt = null,
        public ?DateTimeImmutable $failedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?PaymentFulfillmentStatus $fulfillmentStatus = null,
        public ?DateTimeImmutable $fulfillmentCompletedAt = null,
        public int $fulfillmentAttempts = 0,
        public ?DateTimeImmutable $lastFulfillmentAttemptAt = null,
    ) {}

    public function assignProviderReference(string $reference): void
    {
        $this->providerReference = trim($reference) !== '' ? trim($reference) : null;
    }

    public function confirm(?DateTimeImmutable $now = null): void
    {
        if ($this->status === PaymentStatus::CONFIRMED) { return; }
        if ($this->status === PaymentStatus::FAILED) { throw new InvalidPaymentTransitionException('Un paiement échoué ne peut pas être confirmé.'); }
        $this->status = PaymentStatus::CONFIRMED;
        $this->confirmedAt ??= $now ?? new DateTimeImmutable();
        $this->failedAt = null;
        $this->fulfillmentStatus = PaymentFulfillmentStatus::PENDING;
        $this->fulfillmentCompletedAt = null;
    }

    public function fail(?DateTimeImmutable $now = null): void
    {
        if ($this->status === PaymentStatus::FAILED) { return; }
        if ($this->status === PaymentStatus::CONFIRMED) { throw new InvalidPaymentTransitionException('Un paiement confirmé ne peut pas être rétrogradé.'); }
        $this->status = PaymentStatus::FAILED;
        $this->failedAt ??= $now ?? new DateTimeImmutable();
        $this->fulfillmentStatus = null;
        $this->fulfillmentCompletedAt = null;
    }

    public function ensureFulfillmentPending(): void
    {
        if ($this->status !== PaymentStatus::CONFIRMED) {
            throw new InvalidPaymentTransitionException('Seul un paiement confirmé peut être traité.');
        }

        if ($this->fulfillmentStatus !== PaymentFulfillmentStatus::COMPLETED) {
            $this->fulfillmentStatus = PaymentFulfillmentStatus::PENDING;
            $this->fulfillmentCompletedAt = null;
        }
    }

    public function registerFulfillmentAttempt(?DateTimeImmutable $now = null): void
    {
        $this->ensureFulfillmentPending();
        $this->fulfillmentAttempts++;
        $this->lastFulfillmentAttemptAt = $now ?? new DateTimeImmutable();
    }

    public function completeFulfillment(?DateTimeImmutable $now = null): void
    {
        if ($this->status !== PaymentStatus::CONFIRMED) {
            throw new InvalidPaymentTransitionException('Seul un paiement confirmé peut être finalisé.');
        }

        $this->fulfillmentStatus = PaymentFulfillmentStatus::COMPLETED;
        $this->fulfillmentCompletedAt ??= $now ?? new DateTimeImmutable();
    }

    public function isFulfillmentCompleted(): bool
    {
        return $this->fulfillmentStatus === PaymentFulfillmentStatus::COMPLETED;
    }
}
