<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Websymphonie\PaymentContext\Application\Exception\PaymentVerificationException;
use Websymphonie\PaymentContext\Infrastructure\Service\KkiaPaySdkClientInterface;
use Websymphonie\PaymentContext\Infrastructure\Service\KkiaPayTransactionVerifier;

final class KkiaPayTransactionVerifierTest extends TestCase
{
    public function testItMapsAnSdkSuccessResponseToTheInternalDto(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->expects(self::once())
            ->method('verifyTransaction')
            ->with('transaction-success')
            ->willReturn((object) [
                'transactionId' => 'transaction-success',
                'status' => 'SUCCESS',
                'amount' => 25000,
                'partnerId' => 'payment-uuid',
                'currency' => 'xof',
            ]);

        $transaction = (new KkiaPayTransactionVerifier($client))->verify('transaction-success');

        self::assertTrue($transaction->successful);
        self::assertSame(25000, $transaction->amount);
        self::assertSame('payment-uuid', $transaction->partnerId);
        self::assertSame('XOF', $transaction->currency);
    }

    public function testItMapsAnSdkFailedResponseWithoutConfirmingIt(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willReturn((object) [
            'transactionId' => 'transaction-failed',
            'status' => 'FAILED',
            'amount' => '1000',
            'partnerId' => 'payment-uuid',
        ]);

        $transaction = (new KkiaPayTransactionVerifier($client))->verify('transaction-failed');

        self::assertFalse($transaction->successful);
        self::assertSame(1000, $transaction->amount);
        self::assertNull($transaction->currency);
    }

    public function testPendingProviderStatusIsRetryableAndDoesNotBecomeFailed(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willReturn((object) [
            'transactionId' => 'transaction-pending',
            'status' => 'PENDING',
            'amount' => 1000,
            'partnerId' => 'payment-uuid',
        ]);

        try {
            (new KkiaPayTransactionVerifier($client))->verify('transaction-pending');
            self::fail('Expected a pending provider status to be rejected.');
        } catch (PaymentVerificationException $exception) {
            self::assertTrue($exception->retryable);
            self::assertSame('KkiaPay transaction is not in a terminal state.', $exception->getMessage());
        }
    }

    public function testSdkTransportFailureIsUnavailableAndRetryable(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willThrowException(new RuntimeException('network down'));

        try {
            (new KkiaPayTransactionVerifier($client))->verify('transaction-unavailable');
            self::fail('Expected provider unavailability to be rejected.');
        } catch (PaymentVerificationException $exception) {
            self::assertTrue($exception->retryable);
            self::assertSame('KkiaPay verification is temporarily unavailable.', $exception->getMessage());
        }
    }

    public function testSdkReturnedHttpStatusIsUnavailableAndRetryable(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willReturn(503);

        try {
            (new KkiaPayTransactionVerifier($client))->verify('transaction-unavailable');
            self::fail('Expected provider unavailability to be rejected.');
        } catch (PaymentVerificationException $exception) {
            self::assertTrue($exception->retryable);
            self::assertSame('KkiaPay verification returned HTTP 503.', $exception->getMessage());
        }
    }

    public function testMalformedVerificationResponseIsNotRetryable(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willReturn((object) ['transactionId' => 'transaction-success']);

        try {
            (new KkiaPayTransactionVerifier($client))->verify('transaction-success');
            self::fail('Expected the malformed response to be rejected.');
        } catch (PaymentVerificationException $exception) {
            self::assertFalse($exception->retryable);
        }
    }

    public function testNestedSdkDataIsMappedWithoutLeakingTheSdkObject(): void
    {
        $client = self::createMock(KkiaPaySdkClientInterface::class);
        $client->method('verifyTransaction')->willReturn((object) [
            'data' => (object) [
                'transactionId' => 'transaction-success',
                'status' => 'SUCCESS',
                'amount' => 1000,
                'partnerId' => 'payment-uuid',
            ],
        ]);

        $transaction = (new KkiaPayTransactionVerifier($client))->verify('transaction-success');

        self::assertTrue($transaction->successful);
        self::assertSame('transaction-success', $transaction->transactionId);
        self::assertNull($transaction->currency);
    }
}
