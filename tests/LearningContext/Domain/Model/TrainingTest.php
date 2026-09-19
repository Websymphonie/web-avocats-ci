<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\InvalidTrainingDetailsException;
use Websymphonie\LearningContext\Domain\Model\Training;

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
        $training->publish();
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
        $training->publish();
        $training->archive();

        self::assertSame(TrainingStatus::ARCHIVED, $training->status);
    }

    public function testPublishRequiresTitleSummaryAndDescription(): void
    {
        $training = $this->training();
        $training->update('', 'cours-de-test', '', '<p></p>', TrainingVisibility::PUBLIC, TrainingAccessType::FREE);

        $this->expectException(InvalidTrainingDetailsException::class);
        $training->publish();
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
