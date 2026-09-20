<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Websymphonie\PaymentContext\Application\Exception\PaymentVerificationException;
use Websymphonie\PaymentContext\Application\Model\VerifiedPaymentTransaction;
use Websymphonie\PaymentContext\Application\Service\PaymentTransactionVerifierInterface;

final readonly class KkiaPayTransactionVerifier implements PaymentTransactionVerifierInterface
{
    public function __construct(
        private KkiaPaySdkClientInterface $client,
    ) {
    }

    public function verify(string $transactionId): VerifiedPaymentTransaction
    {
        $transactionId = trim($transactionId);
        if ($transactionId === '') {
            throw PaymentVerificationException::invalid('KkiaPay transaction id is empty.');
        }

        try {
            $response = $this->client->verifyTransaction($transactionId);
        } catch (\Throwable $exception) {
            throw PaymentVerificationException::unavailable('KkiaPay verification is temporarily unavailable.', $exception);
        }

        if (is_int($response)) {
            throw PaymentVerificationException::unavailable(sprintf('KkiaPay verification returned HTTP %d.', $response));
        }

        if (!is_object($response)) {
            throw PaymentVerificationException::invalid('KkiaPay verification returned an invalid response.');
        }

        $payload = get_object_vars($response);
        if (isset($payload['data'])) {
            if (!is_object($payload['data'])) {
                throw PaymentVerificationException::invalid('KkiaPay verification returned an invalid data object.');
            }
            $payload = get_object_vars($payload['data']);
        }

        $verifiedTransactionId = $payload['transactionId'] ?? null;
        $partnerId = $payload['partnerId'] ?? null;
        $amount = $payload['amount'] ?? null;
        $status = $payload['status'] ?? null;
        if (!is_string($verifiedTransactionId) || trim($verifiedTransactionId) === '' || !is_string($partnerId) || trim($partnerId) === '' || !is_numeric($amount) || !is_string($status) || trim($status) === '') {
            throw PaymentVerificationException::invalid('KkiaPay verification omitted required transaction fields.');
        }

        $normalizedAmount = (int)$amount;
        if ($normalizedAmount <= 0 || (float)$amount !== (float)$normalizedAmount) {
            throw PaymentVerificationException::invalid('KkiaPay verification returned an invalid amount.');
        }

        $normalizedStatus = strtoupper(trim($status));
        $successful = match ($normalizedStatus) {
            'SUCCESS' => true,
            'FAILED' => false,
            'PENDING' => throw PaymentVerificationException::unavailable('KkiaPay transaction is not in a terminal state.'),
            default => throw PaymentVerificationException::invalid(sprintf('KkiaPay returned unsupported transaction status "%s".', $normalizedStatus)),
        };

        $currency = $payload['currency'] ?? null;

        return new VerifiedPaymentTransaction(
            transactionId: trim($verifiedTransactionId),
            successful: $successful,
            amount: $normalizedAmount,
            partnerId: trim($partnerId),
            currency: is_string($currency) && trim($currency) !== '' ? strtoupper(trim($currency)) : null,
        );
    }
}
