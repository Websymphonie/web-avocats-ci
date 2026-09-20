<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Presenter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Websymphonie\PaymentContext\Application\Model\PaymentFulfillmentResult;
use Websymphonie\PaymentContext\Application\Usecase\Command\FulfillConfirmedPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\CommandHandler\FulfillConfirmedPaymentHandler;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;

#[AsCommand(
    name: 'app:payment:reconcile-fulfillment',
    description: 'Retente l’activation Learning des paiements confirmés.',
)]
final class ReconcilePaymentFulfillmentConsole extends Command
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly FulfillConfirmedPaymentHandler $fulfillmentHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('payment', null, InputOption::VALUE_REQUIRED, 'UUID du paiement à traiter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $paymentUuid = $input->getOption('payment');
        $payments = [];
        $alreadyCompleted = 0;

        if (is_string($paymentUuid) && $paymentUuid !== '') {
            try {
                $payment = $this->payments->getByUuid($paymentUuid);
            } catch (\Throwable) {
                $io->error('Le paiement demandé est introuvable ou invalide.');
                return Command::FAILURE;
            }

            if ($payment->status !== PaymentStatus::CONFIRMED) {
                $io->error('Seul un paiement confirmé peut être réconcilié.');
                return Command::FAILURE;
            }

            if ($payment->isFulfillmentCompleted()) {
                $alreadyCompleted = 1;
            } else {
                $payments = [$payment];
            }
        } else {
            $payments = $this->payments->listPendingFulfillment();
        }

        $completed = 0;
        $failed = 0;
        foreach ($payments as $payment) {
            try {
                $result = ($this->fulfillmentHandler)(new FulfillConfirmedPaymentCommand($payment->uuid));
                if ($result === PaymentFulfillmentResult::ALREADY_COMPLETED) {
                    $alreadyCompleted++;
                } else {
                    $completed++;
                }
            } catch (\Throwable) {
                $failed++;
            }
        }

        $io->table(['scanned', 'completed', 'already_completed', 'failed'], [[
            count($payments) + $alreadyCompleted,
            $completed,
            $alreadyCompleted,
            $failed,
        ]]);

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
