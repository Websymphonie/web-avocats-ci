<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Usecase\Command\FailPaymentCommand;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Domain\Event\PaymentFailedEvent;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final readonly class FailPaymentHandler implements CommandHandler
{
    public function __construct(private PaymentRepositoryInterface $payments, private TrainingCatalogInterface $trainings, private EventDispatcher $eventDispatcher) {}

    public function __invoke(FailPaymentCommand $command): void
    {
        $payment = $this->payments->getByUuid($command->paymentUuid);
        $wasFailed = $payment->status === PaymentStatus::FAILED;
        $training = $this->trainings->getById($payment->trainingId);
        $payment->fail();
        $payment = $this->payments->save($payment);
        if (!$wasFailed) {
            $payment->emitEvent(new PaymentFailedEvent($payment->uuid, $payment->userId, $payment->trainingId, $payment->amount, $payment->currency, $training->title));
            $this->eventDispatcher->dispatch($payment->releaseEvents());
        }
    }
}
