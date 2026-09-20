<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Application\Usecase\QueryHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\PaymentContext\Application\Model\TrainingReference;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPaymentListQuery;
use Websymphonie\PaymentContext\Application\Usecase\QueryHandler\GetPaymentListHandler;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;

final class GetPaymentListHandlerTest extends TestCase
{
    public function testTrainingLookupIsBatchedAndMissingTrainingDoesNotBreakTheList(): void
    {
        $payments = [
            new Payment(1, 'payment-1', 10, 100, null, 15000, 'XOF', provider: PaymentProvider::FAKE),
            new Payment(2, 'payment-2', 11, 200, null, 20000, 'XOF', provider: PaymentProvider::FAKE),
        ];

        $paymentRepository = $this->createMock(PaymentRepositoryInterface::class);
        $paymentRepository->expects(self::once())->method('list')->with(1, 25, false)->willReturn($payments);

        $trainingCatalog = $this->createMock(TrainingCatalogInterface::class);
        $trainingCatalog
            ->expects(self::once())
            ->method('getByIds')
            ->with([100, 200])
            ->willReturn([new TrainingReference(100, 'training-100', 'Formation 100', 'PUBLISHED', 'PAID')]);

        $users = $this->createMock(UserDirectoryInterface::class);
        $users->expects(self::once())->method('getByIds')->with([10, 11])->willReturn([]);

        $items = (new GetPaymentListHandler($paymentRepository, $trainingCatalog, $users))(new GetPaymentListQuery());

        self::assertCount(2, $items);
        self::assertSame('Formation 100', $items[0]->training?->title);
        self::assertNull($items[1]->training);
    }
}
