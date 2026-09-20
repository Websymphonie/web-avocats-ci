<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Infrastructure\Webhook;

use Psr\Log\LoggerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Uid\Uuid;
use Websymphonie\PaymentContext\Application\Exception\PaymentVerificationException;
use Websymphonie\PaymentContext\Application\Service\PaymentTransactionVerifierInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\FailPaymentCommand;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\PaymentNotFoundException;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

#[AsRemoteEventConsumer('kkiapay')]
final readonly class KkiaPayWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private PaymentTransactionVerifierInterface $verifier,
        private CommandBus $commandBus,
        private LoggerInterface $logger,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        $payload = $event->getPayload();
        $partnerId = is_string($payload['partnerId'] ?? null) ? trim($payload['partnerId']) : '';
        $transactionId = is_string($payload['transactionId'] ?? null) ? trim($payload['transactionId']) : '';
        if (!Uuid::isValid($partnerId) || $transactionId === '') {
            $this->logger->warning('Rejected KkiaPay webhook correlation.', ['event' => $event->getName()]);
            return;
        }

        try {
            $payment = $this->payments->getByUuid($partnerId);
        } catch (PaymentNotFoundException) {
            $this->logger->warning('KkiaPay webhook referenced an unknown payment.', ['paymentUuid' => $partnerId, 'event' => $event->getName()]);
            return;
        }

        if ($payment->provider !== PaymentProvider::KKIAPAY) {
            $this->logger->warning('KkiaPay webhook referenced a payment owned by another provider.', ['paymentUuid' => $partnerId]);
            return;
        }
        if ($payment->providerReference !== null && $payment->providerReference !== $transactionId) {
            $this->logger->warning('KkiaPay provider reference conflict.', ['paymentUuid' => $partnerId, 'providerReference' => $payment->providerReference, 'event' => $event->getName()]);
            return;
        }

        try {
            $verified = $this->verifier->verify($transactionId);
        } catch (PaymentVerificationException $exception) {
            $this->logger->warning('KkiaPay transaction verification failed.', ['paymentUuid' => $partnerId, 'event' => $event->getName(), 'retryable' => $exception->retryable]);
            if ($exception->retryable) {
                throw $exception;
            }
            return;
        }

        if ($verified->transactionId !== $transactionId || $verified->partnerId !== $partnerId || $verified->amount !== $payment->amount || ($verified->currency !== null && $verified->currency !== $payment->currency)) {
            $this->logger->warning('KkiaPay transaction verification mismatch.', ['paymentUuid' => $partnerId, 'event' => $event->getName(), 'result' => 'rejected']);
            return;
        }

        if ($event->getName() === 'transaction.success') {
            if (!$verified->successful || $payment->status === PaymentStatus::FAILED) {
                $this->logger->warning('KkiaPay success event was not confirmable.', ['paymentUuid' => $partnerId]);
                return;
            }
            $this->commandBus->handle(new ConfirmPaymentCommand($payment->uuid, $transactionId));
            return;
        }

        if ($verified->successful || $payment->status === PaymentStatus::CONFIRMED) {
            $this->logger->warning('KkiaPay failed event was not applied.', ['paymentUuid' => $partnerId]);
            return;
        }
        $this->commandBus->handle(new FailPaymentCommand($payment->uuid));
    }
}
