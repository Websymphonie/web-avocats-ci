<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Training;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Websymphonie\LearningContext\Application\Usecase\Query\GetPublicTrainingListQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingCategory\GetTrainingCategoryListQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingTag\GetTrainingTagListQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;
use Websymphonie\LearningContext\Domain\Model\TrainingCategoryListResult;
use Websymphonie\LearningContext\Domain\Model\TrainingListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\SharedContext\Domain\Service\Context\ContextServiceInterface;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/formations', name: 'web_trainings_')]
final class GetPublicTrainingListController extends AbstractController
{
    public function __construct(private readonly MediaPublicUrlResolverInterface $mediaUrls)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function __invoke(Request $request, ContextServiceInterface $context): Response
    {
        $categorySlug = trim((string) $request->query->get('categorie', ''));
        /** @var TrainingCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetTrainingCategoryListQuery(limit: 100));
        $selectedCategory = $this->findCategoryBySlug($categoryResult->items, $categorySlug);
        $type = TrainingType::tryFrom(strtoupper(trim((string) $request->query->get('type', ''))));
        $accessType = TrainingAccessType::tryFrom(strtoupper(trim((string) $request->query->get('acces', ''))));

        /** @var TrainingListResult $trainings */
        $trainings = $this->handleQuery(new GetPublicTrainingListQuery(
            type: $type,
            accessType: $accessType,
            categoryId: $selectedCategory?->id,
            page: max(1, $request->query->getInt('page', 1)),
            limit: max(1, $context->getPaginatorPageSize()),
        ));
        /** @var TrainingTagListResult $tagResult */
        $tagResult = $this->handleQuery(new GetTrainingTagListQuery(limit: 100));

        $mediaIds = array_values(array_filter(array_map(static fn (Training $training): ?int => $training->coverMediaId, $trainings->items)));
        $categoryById = $this->indexCategoriesById($categoryResult->items);
        $tagById = $this->indexTagsById($tagResult->items);

        return $this->render('web/trainings/index.html.twig', [
            'trainings' => $trainings,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'selectedType' => $type,
            'selectedAccessType' => $accessType,
            'accessTypes' => TrainingAccessType::cases(),
            'categoryById' => $categoryById,
            'tagById' => $tagById,
            'mediaUrls' => $this->mediaUrls->resolveMany($mediaIds),
        ]);
    }

    /** @param list<TrainingCategory> $categories */
    private function findCategoryBySlug(array $categories, string $slug): ?TrainingCategory
    {
        if ($slug === '') {
            return null;
        }

        foreach ($categories as $category) {
            if ($category->slug === $slug) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @param list<TrainingCategory> $items
     * @return array<int, TrainingCategory>
     */
    private function indexCategoriesById(array $items): array
    {
        /** @var array<int, TrainingCategory> $indexed */
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->id] = $item;
        }

        return $indexed;
    }

    /**
     * @param list<TrainingTag> $items
     * @return array<int, TrainingTag>
     */
    private function indexTagsById(array $items): array
    {
        /** @var array<int, TrainingTag> $indexed */
        $indexed = [];
        foreach ($items as $item) {
            $indexed[$item->id] = $item;
        }

        return $indexed;
    }
}
