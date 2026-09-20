<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application;

use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryUser;
use Websymphonie\LearningContext\Application\Usecase\Command\EnrollInFreeTrainingCommand;
use Websymphonie\LearningContext\Application\Usecase\CommandHandler\EnrollInFreeTrainingHandler;
use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibility;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\TrainingEnrollmentDeniedException;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;

final class EnrollInFreeTrainingHandlerTest extends TestCase
{
    public function testPublishedFreeLiveCanBeSelfEnrolled(): void
    {
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $enrollments = $this->createMock(EnrollmentRepositoryInterface::class);
        $events = $this->createMock(EventDispatcher::class);
        $events->expects(self::once())->method('dispatch')->with(self::isType('array'));
        $users = $this->createMock(UserDirectoryInterface::class);
        $trainings->method('getById')->willReturn($this->live(TrainingAccessType::FREE));
        $users->method('getById')->willReturn(new UserDirectoryUser(2, 'u', 'Alice', 'alice@example.test', true, ['ROLE_AVOCAT']));
        $enrollments->method('findByTrainingAndUser')->willReturn(null);
        $enrollments->expects(self::once())->method('save')->with(self::isInstanceOf(Enrollment::class))->willReturnArgument(0);

        (new EnrollInFreeTrainingHandler($trainings, $enrollments, new TrainingLearnerEligibility($users), $events))(new EnrollInFreeTrainingCommand(10, 2));
    }

    public function testPublishedPaidLiveCannotBeSelfEnrolled(): void
    {
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $enrollments = $this->createMock(EnrollmentRepositoryInterface::class);
        $events = $this->createMock(EventDispatcher::class);
        $users = $this->createMock(UserDirectoryInterface::class);
        $trainings->method('getById')->willReturn($this->live(TrainingAccessType::PAID));
        $users->method('getById')->willReturn(new UserDirectoryUser(2, 'u', 'Alice', 'alice@example.test', true, ['ROLE_AVOCAT']));
        $enrollments->expects(self::never())->method('save');

        $this->expectException(TrainingEnrollmentDeniedException::class);
        (new EnrollInFreeTrainingHandler($trainings, $enrollments, new TrainingLearnerEligibility($users), $events))(new EnrollInFreeTrainingCommand(10, 2));
    }

    private function live(TrainingAccessType $accessType): Training
    {
        return new Training(10, 'uuid', TrainingType::LIVE, 'Live', 'live', 'Résumé', 'Description', TrainingVisibility::PUBLIC, $accessType, TrainingStatus::PUBLISHED);
    }
}
