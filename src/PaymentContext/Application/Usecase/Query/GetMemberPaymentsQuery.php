<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\Query;

final readonly class GetMemberPaymentsQuery
{
    public function __construct(
        public int $userId,
        public int $page = 1,
        public int $limit = 10,
    ) {
    }
}
