<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;
use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class SaveTrainingOfferHandler implements CommandHandler
{
    public function __construct(private TrainingOfferRepositoryInterface $offers, private TrainingCatalogInterface $trainings) {}

    public function __invoke(SaveTrainingOfferCommand $command): TrainingOffer
    {
        $this->trainings->getById($command->trainingId);
        $offer = $this->offers->findByTrainingId($command->trainingId);
        if ($offer === null) {
            return $this->offers->save(new TrainingOffer(0, '', $command->trainingId, $command->amount, strtoupper(trim($command->currency)), $command->active));
        }
        $offer->update($command->amount, $command->currency, $command->active);
        return $this->offers->save($offer);
    }
}
