<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicyInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetAccessibleLiveJoinDetailsQuery;
use Websymphonie\LearningContext\Application\Usecase\QueryHandler\GetAccessibleLiveJoinDetailsHandler;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\TrainingAccessDeniedException;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final class GetAccessibleLiveJoinDetailsHandlerTest extends TestCase
{
    public function testItReturnsTheManagedJoinDetailsAfterTheAccessCheck(): void
    {
        $training = $this->liveTraining(10);
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $trainings->expects(self::once())->method('getByUuid')->with('live-uuid')->willReturn($training);
        $policy = $this->createMock(TrainingAccessPolicyInterface::class);
        $policy->expects(self::once())->method('assertCanAccess')->with(10, 2);

        $details = (new GetAccessibleLiveJoinDetailsHandler($trainings, $policy))(new GetAccessibleLiveJoinDetailsQuery('live-uuid', 2));

        self::assertSame('https://meet.example.test/live', $details->joinUrl);
    }

    public function testTheRequestedTrainingIdIsUsedForTheIdorCheck(): void
    {
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $trainings->method('getByUuid')->willReturn($this->liveTraining(11));
        $policy = $this->createMock(TrainingAccessPolicyInterface::class);
        $policy->expects(self::once())->method('assertCanAccess')->with(11, 2)->willThrowException(new TrainingAccessDeniedException());

        $this->expectException(TrainingAccessDeniedException::class);
        (new GetAccessibleLiveJoinDetailsHandler($trainings, $policy))(new GetAccessibleLiveJoinDetailsQuery('live-b-uuid', 2));
    }

    public function testInPersonLiveDoesNotExposeAJoinLink(): void
    {
        $training = new Training(10, 'uuid-10', TrainingType::LIVE, 'Live', 'live-10', 'Résumé', 'Description', TrainingVisibility::PUBLIC, TrainingAccessType::FREE, liveDetails: new LiveTrainingDetails(1, 'details-uuid', 10, new DateTimeImmutable('+1 day 10:00'), new DateTimeImmutable('+1 day 11:00'), LiveDeliveryMode::IN_PERSON, location: 'Maison de l’Avocat', joinUrl: 'https://meet.example.test/live'));
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $trainings->method('getByUuid')->willReturn($training);
        $policy = $this->createMock(TrainingAccessPolicyInterface::class);

        $this->expectException(\Websymphonie\LearningContext\Domain\Exception\InvalidLiveTrainingDetailsException::class);
        (new GetAccessibleLiveJoinDetailsHandler($trainings, $policy))(new GetAccessibleLiveJoinDetailsQuery('live-uuid', 2));
    }

    private function liveTraining(int $id): Training
    {
        return new Training($id, 'uuid-' . $id, TrainingType::LIVE, 'Live', 'live-' . $id, 'Résumé', 'Description', TrainingVisibility::PUBLIC, TrainingAccessType::FREE, liveDetails: new LiveTrainingDetails(1, 'details-uuid', $id, new DateTimeImmutable('+1 day 10:00'), new DateTimeImmutable('+1 day 11:00'), LiveDeliveryMode::ONLINE, joinUrl: 'https://meet.example.test/live'));
    }
}
