<?php

declare(strict_types=1);

namespace Websymphonie\LearningContext\Application\Service;

use Websymphonie\LearningContext\Domain\Exception\TrainingTaxonomyAssociationNotFoundException;
use Websymphonie\LearningContext\Domain\Repository\TrainingCategoryRepositoryInterface;
use Websymphonie\LearningContext\Domain\Repository\TrainingTagRepositoryInterface;

final readonly class TrainingClassificationValidator
{
    public function __construct(private TrainingCategoryRepositoryInterface $categories, private TrainingTagRepositoryInterface $tags) {}

    /**
     * @param list<int> $categoryIds
     * @param list<int> $tagIds
     * @return array{list<int>, list<int>}
     */
    public function validate(array $categoryIds, array $tagIds): array
    {
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        $tagIds = array_values(array_unique(array_map('intval', $tagIds)));
        $categories = $this->categories->findByIds($categoryIds);
        foreach ($categoryIds as $id) { if (!in_array($id, array_map(static fn ($item): int => $item->id, $categories), true)) { throw TrainingTaxonomyAssociationNotFoundException::withTypeAndId('La catégorie de formation', $id); } }
        $tags = $this->tags->findByIds($tagIds);
        foreach ($tagIds as $id) { if (!in_array($id, array_map(static fn ($item): int => $item->id, $tags), true)) { throw TrainingTaxonomyAssociationNotFoundException::withTypeAndId('Le tag de formation', $id); } }
        return [$categoryIds, $tagIds];
    }
}
