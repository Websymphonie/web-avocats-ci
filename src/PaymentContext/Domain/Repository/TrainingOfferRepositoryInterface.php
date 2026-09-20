<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Repository;

use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;

interface TrainingOfferRepositoryInterface
{
    public function save(TrainingOffer $offer): TrainingOffer;
    public function getById(int $id): TrainingOffer;
    public function findByTrainingId(int $trainingId): ?TrainingOffer;

    /** @return list<TrainingOffer> */
    public function list(int $page, int $limit): array;
}
