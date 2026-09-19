<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LearningContext\Domain\Model;

use PHPUnit\Framework\TestCase;
use Websymphonie\LearningContext\Application\Service\TrainingClassificationValidator;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomyAssociationNotFoundException;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;

final class TrainingClassificationTest extends TestCase
{
    public function testClassificationIsReplacedAndDeduplicated(): void
    {
        $training = new Training(1, 'uuid', TrainingType::COURSE, 'Titre', 'titre', 'Résumé', 'Description', TrainingVisibility::PUBLIC, TrainingAccessType::FREE);
        $training->replaceClassification([3, 3, 1], [5, 5, 2]);
        self::assertSame([3, 1], $training->categoryIds);
        self::assertSame([5, 2], $training->tagIds);
        $training->replaceClassification([], []);
        self::assertSame([], $training->categoryIds);
        self::assertSame([], $training->tagIds);
    }

    public function testValidatorRejectsUnknownClassificationIds(): void
    {
        $categories = $this->createMock(TrainingCategoryRepositoryInterface::class);
        $tags = $this->createMock(TrainingTagRepositoryInterface::class);
        $categories->method('findByIds')->willReturn([new TrainingCategory(1, 'category-uuid', 'Droit', 'droit')]);
        $tags->method('findByIds')->willReturn([new TrainingTag(2, 'tag-uuid', 'Fiscalité', 'fiscalite')]);

        $validator = new TrainingClassificationValidator($categories, $tags);

        $this->expectException(TrainingTaxonomyAssociationNotFoundException::class);
        $validator->validate([1], [999]);
    }

    public function testValidatorReturnsExistingDeduplicatedClassificationIds(): void
    {
        $categories = $this->createMock(TrainingCategoryRepositoryInterface::class);
        $tags = $this->createMock(TrainingTagRepositoryInterface::class);
        $categories->method('findByIds')->willReturn([new TrainingCategory(1, 'category-uuid', 'Droit', 'droit')]);
        $tags->method('findByIds')->willReturn([new TrainingTag(2, 'tag-uuid', 'Fiscalité', 'fiscalite')]);

        $validator = new TrainingClassificationValidator($categories, $tags);
        $result = $validator->validate([1, 1], [2, 2]);

        self::assertSame([[1], [2]], $result);
    }
}
