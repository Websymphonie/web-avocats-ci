<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Service\PaidTrainingAccessGranterInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\PaymentDeniedException;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class ConfirmPaymentHandler implements CommandHandler
{
    public function __construct(private PaymentRepositoryInterface $payments, private PaidTrainingAccessGranterInterface $accessGranter) {}

    public function __invoke(ConfirmPaymentCommand $command): void
    {
        $payment = $this->payments->getByUuid($command->paymentUuid);
        $providerReference = trim($command->providerReference);
        if ($providerReference === '' || ($payment->providerReference !== null && $payment->providerReference !== $providerReference)) {
            throw new PaymentDeniedException('La référence du paiement ne correspond pas.');
        }
        $alreadyConfirmed = $payment->status === PaymentStatus::CONFIRMED;
        $payment->assignProviderReference($providerReference);
        $payment->confirm();
        if (!$alreadyConfirmed) {
            $this->payments->save($payment);
            $this->accessGranter->grant($payment->userId, $payment->trainingId);
        }
    }
}
