<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Websymphonie\PaymentContext\Application\Exception\PaymentVerificationException;
use Websymphonie\PaymentContext\Infrastructure\Service\KkiaPayTransactionVerifier;

final class KkiaPayTransactionVerifierTest extends TestCase
{
    public function testItUsesTheKkiaPayServerVerificationContract(): void
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('https://api-sandbox.kkiapay.me/api/v1/transactions/status', $url);
            self::assertSame(['transactionId' => 'transaction-success'], json_decode((string) $options['body'], true, flags: JSON_THROW_ON_ERROR));
            self::assertSame(['X-API-KEY: public-key'], $options['normalized_headers']['x-api-key']);
            self::assertSame(['X-PRIVATE-KEY: private-key'], $options['normalized_headers']['x-private-key']);
            self::assertSame(['X-SECRET-KEY: secret-key'], $options['normalized_headers']['x-secret-key']);

            return new MockResponse(json_encode([
                'transactionId' => 'transaction-success',
                'isPaymentSucces' => true,
                'amount' => 25000,
                'partnerId' => 'payment-uuid',
                'currency' => 'XOF',
            ], JSON_THROW_ON_ERROR));
        });

        $transaction = (new KkiaPayTransactionVerifier($client, 'public-key', 'private-key', 'secret-key', true))->verify('transaction-success');

        self::assertTrue($transaction->successful);
        self::assertSame(25000, $transaction->amount);
        self::assertSame('payment-uuid', $transaction->partnerId);
        self::assertSame('XOF', $transaction->currency);
    }

    public function testMalformedVerificationResponseIsNotRetryable(): void
    {
        $client = new MockHttpClient(new MockResponse('{"transactionId":"transaction-success"}'));
        $verifier = new KkiaPayTransactionVerifier($client, 'public-key', 'private-key', 'secret-key', true);

        try {
            $verifier->verify('transaction-success');
            self::fail('Expected the malformed response to be rejected.');
        } catch (PaymentVerificationException $exception) {
            self::assertFalse($exception->retryable);
        }
    }
}
