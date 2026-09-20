<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;
use Websymphonie\PaymentContext\Application\Model\PaymentFulfillmentResult;
use Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\FulfillConfirmedPaymentCommand;
use Websymphonie\PaymentContext\Domain\Exception\PaymentFulfillmentDeniedException;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class FulfillConfirmedPaymentHandler implements CommandHandler
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private PaidTrainingAccessGranterInterface $accessGranter,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(FulfillConfirmedPaymentCommand $command): PaymentFulfillmentResult
    {
        $payment = $this->payments->getByUuid($command->paymentUuid);
        if ($payment->status !== PaymentStatus::CONFIRMED) {
            throw new PaymentFulfillmentDeniedException('Seul un paiement confirmé peut recevoir un accès.');
        }

        if ($payment->isFulfillmentCompleted()) {
            return PaymentFulfillmentResult::ALREADY_COMPLETED;
        }

        try {
            $payment->registerFulfillmentAttempt(new DateTimeImmutable());
            $this->payments->save($payment);
            $this->accessGranter->grant($payment->userId, $payment->trainingId);
            $payment->completeFulfillment(new DateTimeImmutable());
            $this->payments->save($payment);
        } catch (Throwable $exception) {
            $this->logger->error('Le fulfillment du paiement confirmé a échoué.', [
                'paymentUuid' => $payment->uuid,
                'trainingId' => $payment->trainingId,
                'userId' => $payment->userId,
                'provider' => $payment->provider->value,
                'attempt' => $payment->fulfillmentAttempts,
                'failureClass' => $exception::class,
                'failureMessage' => 'Learning access grant or payment fulfillment persistence failed.',
            ]);

            throw $exception;
        }

        return PaymentFulfillmentResult::COMPLETED;
    }
}
