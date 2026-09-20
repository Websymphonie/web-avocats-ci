<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\PaymentContext\Application\Model\PaymentListItem;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPaymentDetailsQuery;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPaymentDetailsHandler implements QueryHandler
{
    public function __construct(private PaymentRepositoryInterface $payments, private TrainingCatalogInterface $trainings, private UserDirectoryInterface $users) {}

    public function __invoke(GetPaymentDetailsQuery $query): PaymentListItem
    {
        $payment = $this->payments->getByUuid($query->uuid);
        $user = $this->users->getById($payment->userId);
        return new PaymentListItem($payment, $this->trainings->getById($payment->trainingId), $user);
    }
}
