<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\FulfillConfirmedPaymentCommand;
use Websymphonie\PaymentContext\Domain\Event\PaymentConfirmedEvent;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\PaymentDeniedException;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class ConfirmPaymentHandler implements CommandHandler
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private FulfillConfirmedPaymentHandler $fulfillmentHandler,
        private ?EventDispatcher $eventDispatcher = null,
    ) {}

    public function __invoke(ConfirmPaymentCommand $command): void
    {
        $payment = $this->payments->getByUuid($command->paymentUuid);
        $providerReference = trim($command->providerReference);
        if ($providerReference === '' || ($payment->providerReference !== null && $payment->providerReference !== $providerReference)) {
            throw new PaymentDeniedException('La référence du paiement ne correspond pas.');
        }
        $alreadyConfirmed = $payment->status === PaymentStatus::CONFIRMED;
        $previousProviderReference = $payment->providerReference;
        $payment->assignProviderReference($providerReference);
        $payment->confirm();
        if (!$alreadyConfirmed) {
            $payment = $this->payments->save($payment);
            $this->eventDispatcher?->dispatch([new PaymentConfirmedEvent($payment->uuid, $payment->userId, $payment->trainingId, $payment->amount, $payment->currency, $payment->provider->value, $payment->providerReference, $payment->confirmedAt ?? new \DateTimeImmutable())]);
        } elseif ($previousProviderReference !== $payment->providerReference || $payment->fulfillmentStatus === null) {
            $payment->ensureFulfillmentPending();
            $payment = $this->payments->save($payment);
        }

        if ($payment->status === PaymentStatus::CONFIRMED && !$payment->isFulfillmentCompleted()) {
            try {
                ($this->fulfillmentHandler)(new FulfillConfirmedPaymentCommand($payment->uuid));
            } catch (\Throwable) {
                // The provider confirmation is durable. Fulfillment remains pending and is retryable.
            }
        }
    }
}
