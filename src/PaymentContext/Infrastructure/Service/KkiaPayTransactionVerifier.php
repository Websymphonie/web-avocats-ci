<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Websymphonie\PaymentContext\Application\Exception\PaymentVerificationException;
use Websymphonie\PaymentContext\Application\Model\VerifiedPaymentTransaction;
use Websymphonie\PaymentContext\Application\Service\PaymentTransactionVerifierInterface;

final readonly class KkiaPayTransactionVerifier implements PaymentTransactionVerifierInterface
{
    private const string PRODUCTION_URL = 'https://api.kkiapay.me';
    private const string SANDBOX_URL = 'https://api-sandbox.kkiapay.me';

    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(KKIAPAY_PUBLIC_KEY)%')]
        private string              $publicKey,
        #[Autowire('%env(KKIAPAY_PRIVATE_KEY)%')]
        private string              $privateKey,
        #[Autowire('%env(KKIAPAY_SECRET_KEY)%')]
        private string              $secretKey,
        #[Autowire('%env(bool:KKIAPAY_SANDBOX)%')]
        private bool                $sandbox,
    )
    {
    }

    public function verify(string $transactionId): VerifiedPaymentTransaction
    {
        $transactionId = trim($transactionId);
        if ($transactionId === '') {
            throw PaymentVerificationException::invalid('KkiaPay transaction id is empty.');
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                ($this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL) . '/api/v1/transactions/status',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->publicKey,
                        'X-PRIVATE-KEY' => $this->privateKey,
                        'X-SECRET-KEY' => $this->secretKey,
                    ],
                    'json' => ['transactionId' => $transactionId],
                ],
            );
            $statusCode = $response->getStatusCode();
            $payload = $response->toArray(false);
        } catch (ExceptionInterface $exception) {
            throw PaymentVerificationException::unavailable('KkiaPay verification is temporarily unavailable.', $exception);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw $statusCode >= 500
                ? PaymentVerificationException::unavailable(sprintf('KkiaPay verification returned HTTP %d.', $statusCode))
                : PaymentVerificationException::invalid(sprintf('KkiaPay verification returned HTTP %d.', $statusCode));
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        $verifiedTransactionId = $payload['transactionId'] ?? null;
        $partnerId = $payload['partnerId'] ?? null;
        $amount = $payload['amount'] ?? null;
        $successful = $payload['isPaymentSucces'] ?? null;
        if (!is_string($verifiedTransactionId) || trim($verifiedTransactionId) === '' || !is_string($partnerId) || trim($partnerId) === '' || !is_bool($successful) || !is_numeric($amount)) {
            throw PaymentVerificationException::invalid('KkiaPay verification omitted required transaction fields.');
        }

        $normalizedAmount = (int)$amount;
        if ($normalizedAmount <= 0 || (float)$amount !== (float)$normalizedAmount) {
            throw PaymentVerificationException::invalid('KkiaPay verification returned an invalid amount.');
        }

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
