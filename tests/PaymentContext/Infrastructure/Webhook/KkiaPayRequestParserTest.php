<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Infrastructure\Webhook;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Webhook\Exception\RejectWebhookException;
use Websymphonie\PaymentContext\Application\Model\VerifiedPaymentTransaction;
use Websymphonie\PaymentContext\Application\Service\PaymentTransactionVerifierInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\FailPaymentCommand;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\PaymentContext\Infrastructure\Webhook\KkiaPayRequestParser;
use Websymphonie\PaymentContext\Infrastructure\Webhook\KkiaPayWebhookConsumer;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

final class KkiaPayRequestParserTest extends TestCase
{
    public function testSuccessPayloadIsConvertedToRemoteEvent(): void
    {
        $transactionId = 'transaction-success';
        $partnerId = Uuid::v7()->toRfc4122();
        $event = (new KkiaPayRequestParser())->parse($this->request([
            'event' => 'transaction.success',
            'transactionId' => $transactionId,
            'isPaymentSucces' => true,
            'amount' => 25000,
            'partnerId' => $partnerId,
        ]), 'webhook-secret');

        self::assertInstanceOf(RemoteEvent::class, $event);
        self::assertSame('transaction.success', $event->getName());
        self::assertSame('transaction.success:' . $transactionId, $event->getId());
    }

    public function testMissingOrInvalidSecretIsRejected(): void
    {
        $this->expectException(RejectWebhookException::class);
        (new KkiaPayRequestParser())->parse($this->request([
            'event' => 'transaction.success',
            'transactionId' => 'transaction-success',
            'isPaymentSucces' => true,
            'amount' => 25000,
            'partnerId' => Uuid::v7()->toRfc4122(),
        ], 'wrong-secret'), 'webhook-secret');
    }

    public function testUnsupportedAndIncoherentPayloadsAreRejected(): void
    {
        $parser = new KkiaPayRequestParser();
        $base = [
            'transactionId' => 'transaction',
            'isPaymentSucces' => true,
            'amount' => 25000,
            'partnerId' => Uuid::v7()->toRfc4122(),
        ];

        $this->expectException(RejectWebhookException::class);
        $parser->parse($this->request($base + ['event' => 'transaction.created']), 'webhook-secret');
    }

    public function testMalformedJsonIsRejected(): void
    {
        $request = Request::create(
            '/webhook/kkiapay',
            'POST',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_KKIAPAY_SECRET' => 'webhook-secret',
            ],
            content: '{"event":',
        );

        $this->expectException(RejectWebhookException::class);

        (new KkiaPayRequestParser())->parse($request, 'webhook-secret');
    }

    public function testConsumerConfirmsOnlyAfterServerVerificationAndDuplicateIsIdempotent(): void
    {
        $payment = $this->payment();
        $payments = $this->createMock(PaymentRepositoryInterface::class);
        $payments->method('getByUuid')->willReturn($payment);
        $payments->method('save')->willReturn($payment);
        $verifier = $this->createMock(PaymentTransactionVerifierInterface::class);
        $verifier->expects(self::exactly(2))->method('verify')->willReturn(new VerifiedPaymentTransaction('transaction-success', true, 25000, $payment->uuid));
        $accessGranter = $this->createMock(\Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface::class);
        $accessGranter->expects(self::once())->method('grant')->with(10, 20);
        $confirm = new \Websymphonie\PaymentContext\Application\Usecase\CommandHandler\ConfirmPaymentHandler($payments, $accessGranter);
        $bus = $this->createMock(CommandBus::class);
        $bus->expects(self::exactly(2))->method('handle')->willReturnCallback(function (object $command) use ($confirm): void {
            if ($command instanceof ConfirmPaymentCommand) {
                $confirm($command);
            }
        });

        $event = $this->successEvent($payment->uuid);
        $consumer = new KkiaPayWebhookConsumer($payments, $verifier, $bus, new NullLogger());
        $consumer->consume($event);
        $consumer->consume($event);

        self::assertSame(PaymentStatus::CONFIRMED, $payment->status);
        self::assertSame('transaction-success', $payment->providerReference);
    }

    public function testAmountMismatchDoesNotDispatchConfirmation(): void
    {
        $payment = $this->payment();
        $payments = $this->createMock(PaymentRepositoryInterface::class);
        $payments->method('getByUuid')->willReturn($payment);
        $verifier = $this->createMock(PaymentTransactionVerifierInterface::class);
        $verifier->method('verify')->willReturn(new VerifiedPaymentTransaction('transaction-success', true, 10000, $payment->uuid));
        $bus = $this->createMock(CommandBus::class);
        $bus->expects(self::never())->method('handle');

        (new KkiaPayWebhookConsumer($payments, $verifier, $bus, new NullLogger()))->consume($this->successEvent($payment->uuid));

        self::assertSame(PaymentStatus::PENDING, $payment->status);
    }

    public function testPartnerMismatchDoesNotDispatchConfirmation(): void
    {
        $payment = $this->payment();
        $payments = $this->createMock(PaymentRepositoryInterface::class);
        $payments->method('getByUuid')->willReturn($payment);
        $verifier = $this->createMock(PaymentTransactionVerifierInterface::class);
        $verifier->method('verify')->willReturn(new VerifiedPaymentTransaction('transaction-success', true, 25000, 'another-payment-uuid'));
        $bus = $this->createMock(CommandBus::class);
        $bus->expects(self::never())->method('handle');

        (new KkiaPayWebhookConsumer($payments, $verifier, $bus, new NullLogger()))->consume($this->successEvent($payment->uuid));

        self::assertSame(PaymentStatus::PENDING, $payment->status);
    }

    private function request(array $payload, string $secret = 'webhook-secret'): Request
    {
        return Request::create(
            '/webhook/kkiapay',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_KKIAPAY_SECRET' => $secret],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    private function payment(): Payment
    {
        return new Payment(1, Uuid::v7()->toRfc4122(), 10, 20, 30, 25000, 'XOF', provider: PaymentProvider::KKIAPAY, idempotencyKey: 'checkout-kkiapay');
    }

    private function successEvent(string $partnerId): RemoteEvent
    {
        return new RemoteEvent('transaction.success', 'transaction.success:transaction-success', [
            'event' => 'transaction.success',
            'transactionId' => 'transaction-success',
            'isPaymentSucces' => true,
            'amount' => 25000,
            'partnerId' => $partnerId,
        ]);
    }
}
