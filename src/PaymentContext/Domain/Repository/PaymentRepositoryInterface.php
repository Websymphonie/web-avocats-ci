<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Repository;

use Websymphonie\PaymentContext\Domain\Model\Payment;

interface PaymentRepositoryInterface
{
    public function save(Payment $payment): Payment;
    public function getById(int $id): Payment;
    public function getByUuid(string $uuid): Payment;
    public function findByUserAndIdempotencyKey(int $userId, string $idempotencyKey): ?Payment;
    public function findPendingByUserAndTraining(int $userId, int $trainingId): ?Payment;
    public function findByProviderReference(string $provider, string $reference): ?Payment;

    /** @return list<Payment> */
    public function list(int $page, int $limit, bool $pendingFulfillmentOnly = false): array;

    /** @return list<Payment> */
    public function listByUser(int $userId, int $page, int $limit): array;

    public function countByUser(int $userId): int;

    /** @return list<Payment> */
    public function listPendingFulfillment(?string $paymentUuid = null): array;
}
