<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Model;

use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;

final readonly class TrainingOfferListItem
{
    public function __construct(public TrainingOffer $offer, public TrainingReference $training) {}
}
