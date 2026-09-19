<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryUser;
use Websymphonie\LearningContext\Application\Service\TrainingAccessPolicy;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Model\Enrollment;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Repository\EnrollmentRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingRepositoryInterface;

final class TrainingAccessPolicyTest extends TestCase
{
    public function testOnlyPublishedTrainingWithActiveEnrollmentIsAccessible(): void
    {
        /** @var TrainingRepositoryInterface&MockObject $trainings */
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        /** @var EnrollmentRepositoryInterface&MockObject $enrollments */
        $enrollments = $this->createMock(EnrollmentRepositoryInterface::class);
        /** @var UserDirectoryInterface&MockObject $users */
        $users = $this->createMock(UserDirectoryInterface::class);
        $users->method('getById')->willReturn(new UserDirectoryUser(2, 'u', 'Alice', 'alice@example.test', true));
        $trainings->method('getById')->willReturn($this->training(TrainingStatus::PUBLISHED));
        $enrollments->method('findByTrainingAndUser')->willReturn(new Enrollment(1, 'e', 10, 2, EnrollmentStatus::ACTIVE, EnrollmentSource::SELF_SERVICE));
        $policy = new TrainingAccessPolicy($trainings, $enrollments, $users);
        self::assertTrue($policy->canAccess(10, 2));
    }

    public function testRevokedEnrollmentIsDenied(): void
    {
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $enrollments = $this->createMock(EnrollmentRepositoryInterface::class);
        $users = $this->createMock(UserDirectoryInterface::class);
        $users->method('getById')->willReturn(new UserDirectoryUser(2, 'u', 'Alice', 'alice@example.test', true));
        $trainings->method('getById')->willReturn($this->training(TrainingStatus::PUBLISHED));
        $enrollments->method('findByTrainingAndUser')->willReturn(new Enrollment(1, 'e', 10, 2, EnrollmentStatus::REVOKED));
        self::assertFalse((new TrainingAccessPolicy($trainings, $enrollments, $users))->canAccess(10, 2));
    }

    public function testPublishedLiveWithActiveEnrollmentIsAccessibleLikeACourse(): void
    {
        $trainings = $this->createMock(TrainingRepositoryInterface::class);
        $enrollments = $this->createMock(EnrollmentRepositoryInterface::class);
        $users = $this->createMock(UserDirectoryInterface::class);
        $users->method('getById')->willReturn(new UserDirectoryUser(2, 'u', 'Alice', 'alice@example.test', true));
        $trainings->method('getById')->willReturn(new Training(10, 't', TrainingType::LIVE, 'Live', 'live', 'Résumé', 'Description', TrainingVisibility::PUBLIC, TrainingAccessType::FREE, TrainingStatus::PUBLISHED));
        $enrollments->method('findByTrainingAndUser')->willReturn(new Enrollment(1, 'e', 10, 2, EnrollmentStatus::ACTIVE, EnrollmentSource::SELF_SERVICE));

        self::assertTrue((new TrainingAccessPolicy($trainings, $enrollments, $users))->canAccess(10, 2));
    }

    private function training(TrainingStatus $status): Training
    {
        return new Training(10, 't', TrainingType::COURSE, 'Course', 'course', 'Summary', 'Description', TrainingVisibility::PUBLIC, TrainingAccessType::FREE, $status);
    }
}
