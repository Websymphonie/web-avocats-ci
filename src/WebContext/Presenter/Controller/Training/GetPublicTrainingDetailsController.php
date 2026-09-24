<?php

declare(strict_types=1);

namespace Websymphonie\WebContext\Presenter\Controller\Training;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\IdentityContext\Application\Service\User\CurrentUserProvider;
use Websymphonie\LearningContext\Application\Service\TrainingLearnerEligibilityInterface;
use Websymphonie\LearningContext\Application\Usecase\Query\GetMemberTrainingSummariesQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\GetPublicTrainingBySlugQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingCategory\GetTrainingCategoryListQuery;
use Websymphonie\LearningContext\Application\Usecase\Query\TrainingTag\GetTrainingTagListQuery;
use Websymphonie\LearningContext\Domain\Exception\TrainingNotFoundException;
use Websymphonie\LearningContext\Domain\Model\Training;
use Websymphonie\LearningContext\Domain\Model\TrainingCategory;
use Websymphonie\LearningContext\Domain\Model\TrainingCategoryListResult;
use Websymphonie\LearningContext\Domain\Model\TrainingTag;
use Websymphonie\LearningContext\Domain\Model\TrainingTagListResult;
use Websymphonie\MediaContext\Application\Service\MediaPublicUrlResolverInterface;
use Websymphonie\PaymentContext\Application\Usecase\Query\GetPublicTrainingOfferQuery;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Model\MemberTrainingSummary;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/formations', name: 'web_trainings_')]
final class GetPublicTrainingDetailsController extends AbstractController
{
    public function __construct(
        private readonly RichTextSanitizerInterface $sanitizer,
        private readonly MediaPublicUrlResolverInterface $mediaUrls,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/{slug}', name: 'detail', requirements: ['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(string $slug, CurrentUserProvider $currentUser, TrainingLearnerEligibilityInterface $eligibility): Response
    {
        try {
            $training = $this->handleQuery(new GetPublicTrainingBySlugQuery($slug));
        } catch (TrainingNotFoundException) {
            throw $this->createNotFoundException();
        }

        /** @var TrainingCategoryListResult $categoryResult */
        $categoryResult = $this->handleQuery(new GetTrainingCategoryListQuery(limit: 100));
        /** @var TrainingTagListResult $tagResult */
        $tagResult = $this->handleQuery(new GetTrainingTagListQuery(limit: 100));
        $coverUrls = $training->coverMediaId !== null ? $this->mediaUrls->resolveMany([$training->coverMediaId]) : [];
        $user = $currentUser->user();
        $userId = $user?->id;
        $isEligibleLearner = $userId !== null && $eligibility->isEligible($userId);
        $hasActiveEnrollment = false;
        if ($isEligibleLearner) {
            /** @var list<MemberTrainingSummary> $summaries */
            $summaries = $this->handleQuery(new GetMemberTrainingSummariesQuery($userId));
            foreach ($summaries as $summary) {
                if ($summary->training->id === $training->id) {
                    $hasActiveEnrollment = true;
                    break;
                }
            }
        }
        $offer = $training->accessType === TrainingAccessType::PAID
            ? $this->handleQuery(new GetPublicTrainingOfferQuery($training->id))
            : null;
        $selectedCategory = null;
        $currentCategoryId = $training->categoryIds[0] ?? null;
        foreach ($categoryResult->items as $category) {
            if ($category->id === $currentCategoryId) {
                $selectedCategory = $category;
                break;
            }
        }

        return $this->render('web/trainings/show.html.twig', [
            'training' => $training,
            'categories' => $categoryResult->items,
            'selectedCategory' => $selectedCategory,
            'categoryById' => $this->indexCategoriesById($categoryResult->items),
            'tagById' => $this->indexTagsById($tagResult->items),
            'coverUrl' => $coverUrls[$training->coverMediaId] ?? null,
            'safeDescription' => $this->sanitizer->sanitize($training->description),
            'isAuthenticated' => $userId !== null,
            'isEligibleLearner' => $isEligibleLearner,
            'hasActiveEnrollment' => $hasActiveEnrollment,
            'offer' => $offer,
            'paymentIdempotencyKey' => $isEligibleLearner && !$hasActiveEnrollment && $offer !== null ? Uuid::v7()->toRfc4122() : null,
        ]);
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
