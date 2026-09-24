<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\PaymentContext\Application\Model\PublicTrainingOffer;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPublicTrainingOfferQuery;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPublicTrainingOfferHandler implements QueryHandler
{
    public function __construct(
        private TrainingCatalogInterface $trainings,
        private TrainingOfferRepositoryInterface $offers,
    ) {
    }

    public function __invoke(GetPublicTrainingOfferQuery $query): ?PublicTrainingOffer
    {
        if (!$this->trainings->getById($query->trainingId)->isPublishedPaid()) {
            return null;
        }

        $offer = $this->offers->findByTrainingId($query->trainingId);
        if ($offer === null || !$offer->active) {
            return null;
        }

        return new PublicTrainingOffer($offer->amount, $offer->currency);
    }
}
