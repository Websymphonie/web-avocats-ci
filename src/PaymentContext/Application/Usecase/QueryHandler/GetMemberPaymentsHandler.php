<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\QueryHandler;

use Websymphonie\PaymentContext\Application\Model\MemberPaymentList;
use Websymphonie\PaymentContext\Application\Model\MemberPaymentSummary;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetMemberPaymentsQuery;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetMemberPaymentsHandler implements QueryHandler
{
    public function __construct(
        private PaymentRepositoryInterface $payments,
        private TrainingCatalogInterface $trainings,
    ) {
    }

    public function __invoke(GetMemberPaymentsQuery $query): MemberPaymentList
    {
        $page = max(1, $query->page);
        $limit = max(1, min(50, $query->limit));
        $payments = $this->payments->listByUser($query->userId, $page, $limit);
        $trainingIds = array_values(array_unique(array_map(static fn ($payment): int => $payment->trainingId, $payments)));
        $trainingById = [];
        foreach ($this->trainings->getByIds($trainingIds) as $training) {
            $trainingById[$training->id] = $training;
        }

        $items = [];
        foreach ($payments as $payment) {
            [$paymentStatusVariant, $accessStatusLabel, $accessStatusVariant] = match ($payment->status) {
                PaymentStatus::CONFIRMED => $payment->isFulfillmentCompleted()
                    ? ['success', 'Accès activé', 'success']
                    : ['success', 'Traitement en cours', 'warning'],
                PaymentStatus::FAILED => ['destructive', 'Non activé', 'secondary'],
                PaymentStatus::PENDING => ['secondary', 'Non activé', 'secondary'],
            };

            $items[] = new MemberPaymentSummary(
                trainingTitle: $trainingById[$payment->trainingId]->title ?? 'Formation indisponible',
                amount: $payment->amount,
                currency: $payment->currency,
                createdAt: $payment->createdAt,
                paymentStatusLabel: $payment->status->label(),
                paymentStatusVariant: $paymentStatusVariant,
                accessStatusLabel: $accessStatusLabel,
                accessStatusVariant: $accessStatusVariant,
            );
        }

        return new MemberPaymentList(
            items: $items,
            total: $this->payments->countByUser($query->userId),
            page: $page,
            limit: $limit,
        );
    }
}
