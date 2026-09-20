<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Service\CurrencyCatalogInterface;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;
use Websymphonie\PaymentContext\Domain\Exception\CurrencyNotFoundException;
use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class SaveTrainingOfferHandler implements CommandHandler
{
    public function __construct(
        private TrainingOfferRepositoryInterface $offers,
        private TrainingCatalogInterface $trainings,
        private CurrencyCatalogInterface $currencies,
    ) {
    }

    public function __invoke(SaveTrainingOfferCommand $command): TrainingOffer
    {
        $this->trainings->getById($command->trainingId);
        $currency = $this->currencies->findActiveByCode($command->currency);
        if ($currency === null) {
            throw CurrencyNotFoundException::withCode($command->currency);
        }

        $offer = $this->offers->findByTrainingId($command->trainingId);
        if ($offer === null) {
            return $this->offers->save(new TrainingOffer(0, '', $command->trainingId, $command->amount, $currency->code, $command->active));
        }
        $offer->update($command->amount, $currency->code, $command->active);
        return $this->offers->save($offer);
    }
}
