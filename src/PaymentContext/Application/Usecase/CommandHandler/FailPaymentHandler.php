<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Usecase\Command\FailPaymentCommand;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class FailPaymentHandler implements CommandHandler
{
    public function __construct(private PaymentRepositoryInterface $payments) {}

    public function __invoke(FailPaymentCommand $command): void
    {
        $payment = $this->payments->getByUuid($command->paymentUuid);
        $payment->fail();
        $this->payments->save($payment);
    }
}
