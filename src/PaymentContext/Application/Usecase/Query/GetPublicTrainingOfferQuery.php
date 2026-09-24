<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Query;

final readonly class GetPublicTrainingOfferQuery
{
    public function __construct(public int $trainingId)
    {
    }
}
