<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Query;

final readonly class GetTrainingOfferListQuery
{
    public function __construct(public int $page = 1, public int $limit = 20) {}
}
