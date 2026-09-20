<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\PaymentContext\Application\Model\TrainingOfferListItem;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetTrainingOfferListQuery;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetTrainingOfferListHandler implements QueryHandler
{
    public function __construct(private TrainingOfferRepositoryInterface $offers, private TrainingCatalogInterface $trainings) {}

    /** @return list<TrainingOfferListItem> */
    public function __invoke(GetTrainingOfferListQuery $query): array
    {
        $offers = $this->offers->list(max(1, $query->page), $query->limit);
        $trainingIds = array_values(array_unique(array_map(static fn ($offer): int => $offer->trainingId, $offers)));
        $trainings = [];
        foreach ($this->trainings->getByIds($trainingIds) as $training) {
            $trainings[$training->id] = $training;
        }
        $items = [];
        foreach ($offers as $offer) {
            $items[] = new TrainingOfferListItem($offer, $trainings[$offer->trainingId] ?? null);
        }
        return $items;
    }
}
