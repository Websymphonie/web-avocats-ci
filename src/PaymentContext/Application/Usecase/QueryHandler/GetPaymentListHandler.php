<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\PaymentContext\Application\Model\PaymentListItem;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPaymentListQuery;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetPaymentListHandler implements QueryHandler
{
    public function __construct(private PaymentRepositoryInterface $payments, private TrainingCatalogInterface $trainings, private UserDirectoryInterface $users) {}

    /** @return list<PaymentListItem> */
    public function __invoke(GetPaymentListQuery $query): array
    {
        $payments = $this->payments->list(max(1, $query->page), $query->limit, $query->pendingFulfillmentOnly);
        $users = [];
        $userIds = array_values(array_unique(array_map(static fn ($payment): int => $payment->userId, $payments)));
        foreach ($this->users->getByIds($userIds) as $user) {
            $users[$user->id] = $user;
        }
        $trainingIds = array_values(array_unique(array_map(static fn ($payment): int => $payment->trainingId, $payments)));
        $trainings = [];
        foreach ($this->trainings->getByIds($trainingIds) as $training) {
            $trainings[$training->id] = $training;
        }
        $items = [];
        foreach ($payments as $payment) {
            $items[] = new PaymentListItem($payment, $trainings[$payment->trainingId] ?? null, $users[$payment->userId] ?? null);
        }
        return $items;
    }
}
