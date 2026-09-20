<?php

declare(strict_types=1);

namespace Websymphonie\Tests\PaymentContext\Application\Usecase\QueryHandler;

use PHPUnit\Framework\TestCase;
use Websymphonie\PaymentContext\Application\Model\TrainingReference;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetTrainingOfferListQuery;
use Websymphonie\PaymentContext\Application\Usecase\QueryHandler\GetTrainingOfferListHandler;
use Websymphonie\PaymentContext\Domain\Model\TrainingOffer;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;

final class GetTrainingOfferListHandlerTest extends TestCase
{
    public function testTrainingLookupIsBatchedAndMissingTrainingIsRepresentedAsNull(): void
    {
        $offers = [
            new TrainingOffer(1, 'offer-1', 100, 15000),
            new TrainingOffer(2, 'offer-2', 200, 20000),
        ];

        $offerRepository = $this->createMock(TrainingOfferRepositoryInterface::class);
        $offerRepository->expects(self::once())->method('list')->with(1, 20)->willReturn($offers);

        $trainingCatalog = $this->createMock(TrainingCatalogInterface::class);
        $trainingCatalog
            ->expects(self::once())
            ->method('getByIds')
            ->with([100, 200])
            ->willReturn([new TrainingReference(100, 'training-100', 'Formation 100', 'PUBLISHED', 'PAID')]);

        $items = (new GetTrainingOfferListHandler($offerRepository, $trainingCatalog))(new GetTrainingOfferListQuery());

        self::assertCount(2, $items);
        self::assertSame('Formation 100', $items[0]->training?->title);
        self::assertNull($items[1]->training);
    }
}
