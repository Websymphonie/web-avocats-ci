<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\PaymentContext\Application\Usecase\Query\GetMemberPaymentStatusQuery;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberPaymentStatusHandler implements QueryHandler
{
    public function __construct(private PaymentRepositoryInterface $payments)
    {
    }

    public function __invoke(GetMemberPaymentStatusQuery $query): Payment
    {
        return $this->payments->getByUuid($query->uuid);
    }
}
