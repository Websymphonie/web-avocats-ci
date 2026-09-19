<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Exception\InvalidTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\LiveTrainingDetails;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Factory\TrainingFactory;

final class TrainingTest extends TestCase
{
    public function testNewTrainingIsACourseDraft(): void
    {
        $training = $this->training();

        self::assertSame(TrainingType::COURSE, $training->type);
        self::assertSame(TrainingStatus::DRAFT, $training->status);
        self::assertSame(TrainingVisibility::PUBLIC, $training->visibility);
        self::assertSame(TrainingAccessType::FREE, $training->accessType);
    }

    public function testPublishSetsPublishedAtAndKeepsSlugStableAfterward(): void
    {
        $training = $this->training();
        $training->publish(1, 0);
        $publishedAt = $training->publishedAt;

        $training->update('Nouveau titre', 'nouveau-slug', 'Nouveau résumé', '<p>Nouvelle description</p>', TrainingVisibility::MEMBER, TrainingAccessType::PAID);

        self::assertSame(TrainingStatus::PUBLISHED, $training->status);
        self::assertNotNull($publishedAt);
        self::assertSame($publishedAt, $training->publishedAt);
        self::assertSame('cours-de-test', $training->slug);
        self::assertSame('Nouveau titre', $training->title);
    }

    public function testArchiveIsASeparateTransition(): void
    {
        $training = $this->training();
        $training->publish(1, 0);
        $training->archive();

        self::assertSame(TrainingStatus::ARCHIVED, $training->status);
    }

    public function testPublishRequiresTitleSummaryAndDescription(): void
    {
        $training = $this->training();
        $training->update('', 'cours-de-test', '', '<p></p>', TrainingVisibility::PUBLIC, TrainingAccessType::FREE);

        $this->expectException(InvalidTrainingDetailsException::class);
        $training->publish(1, 0);
    }

    public function testCourseCannotBePublishedWithoutAModule(): void
    {
        $this->expectException(InvalidTrainingDetailsException::class);

        $this->training()->publish(0, 0);
    }

    public function testCourseCannotBePublishedWithAnEmptyModule(): void
    {
        $this->expectException(InvalidTrainingDetailsException::class);

        $this->training()->publish(1, 1);
    }

    public function testCourseCannotBePublishedWithAnUnreadyLesson(): void
    {
        $this->expectException(InvalidTrainingDetailsException::class);

        $this->training()->publish(1, 0, 1);
    }

    public function testLiveCanBePublishedWithoutCourseModulesWhenDetailsAreValid(): void
    {
        $training = new Training(1, 'uuid', TrainingType::LIVE, 'Live de test', 'live-de-test', 'Résumé', '<p>Description</p>', TrainingVisibility::PUBLIC, TrainingAccessType::FREE, liveDetails: new LiveTrainingDetails(0, '', 1, new \DateTimeImmutable('+1 day 10:00'), new \DateTimeImmutable('+1 day 11:00'), LiveDeliveryMode::ONLINE, joinUrl: 'https://meet.example.test/live'));

        $training->publish();

        self::assertSame(TrainingStatus::PUBLISHED, $training->status);
        self::assertNotNull($training->publishedAt);
    }

    public function testLiveCannotBePublishedWithoutDetails(): void
    {
        $training = new Training(1, 'uuid', TrainingType::LIVE, 'Live de test', 'live-de-test', 'Résumé', '<p>Description</p>', TrainingVisibility::PUBLIC, TrainingAccessType::FREE);

        $this->expectException(InvalidTrainingDetailsException::class);
        $training->publish();
    }

    public function testTrainingTypeCannotChangeWhenMappingAnExistingEntity(): void
    {
        $entity = new TrainingEntity(TrainingType::COURSE);
        $model = new Training(
            id: 1,
            uuid: 'uuid',
            type: TrainingType::LIVE,
            title: 'Live de test',
            slug: 'live-de-test',
            summary: 'Résumé',
            description: '<p>Description</p>',
            visibility: TrainingVisibility::PUBLIC,
            accessType: TrainingAccessType::FREE,
        );

        $this->expectException(InvalidTrainingDetailsException::class);

        (new TrainingFactory())->toEntity($model, $entity);
    }

    private function training(): Training
    {
        return new Training(
            id: 1,
            uuid: 'uuid',
            type: TrainingType::COURSE,
            title: 'Cours de test',
            slug: 'cours-de-test',
            summary: 'Résumé de test',
            description: '<p>Description de test</p>',
            visibility: TrainingVisibility::PUBLIC,
            accessType: TrainingAccessType::FREE,
        );
    }
}
